<?php

/**
 * File containing the Functional\TestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use Buzz\Message\Request as HttpRequest;
use Buzz\Message\Response as HttpResponse;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @var \Buzz\Client\ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $httpHost;

    /**
     * @var string
     * Basic auth login:password
     */
    private $httpAuth;

    protected static $testSuffix;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * The username to use for login.
     * @var string
     */
    private $loginUsername;

    /**
     * The password to use for login.
     * @var string
     */
    private $loginPassword;

    /**
     * If true, a login request is automatically done during setUp().
     * @var bool
     */
    protected $autoLogin = true;

    /**
     * List of REST contentId (/content/objects/12345) created by tests.
     *
     * @var array
     */
    private static $createdContent = array();

    protected function setUp()
    {
        parent::setUp();

        $this->httpHost = getenv('EZP_TEST_REST_HOST') ?: 'localhost';
        $this->httpAuth = getenv('EZP_TEST_REST_AUTH') ?: 'admin:publish';
        list($this->loginUsername, $this->loginPassword) = explode(':', $this->httpAuth);

        $this->httpClient = new \Buzz\Client\Curl();
        $this->httpClient->setVerifyPeer(false);
        $this->httpClient->setTimeout(90);
        $this->httpClient->setOption(CURLOPT_FOLLOWLOCATION, false);

        if ($this->autoLogin) {
            $session = $this->login();
            $this->headers[] = sprintf('Cookie: %s=%s', $session->name, $session->identifier);
            $this->headers[] = sprintf('X-CSRF-Token: %s', $session->csrfToken);
        }
    }

    /**
     * @return HttpResponse
     */
    public function sendHttpRequest(HttpRequest $request)
    {
        $response = new HttpResponse();
        $this->httpClient->send($request, $response);

        return $response;
    }

    protected function getHttpHost()
    {
        return $this->httpHost;
    }

    protected function getLoginUsername()
    {
        return $this->loginUsername;
    }

    protected function getLoginPassword()
    {
        return $this->loginPassword;
    }

    /**
     * @return HttpRequest
     */
    public function createHttpRequest($method, $uri, $contentType = '', $acceptType = '')
    {
        $headers = array_merge(
            $method === 'POST' && $uri === '/api/ezp/v2/user/sessions' ? [] : $this->headers,
            [
                'Content-Type: ' . $this->generateMediaTypeString($contentType),
                'Accept: ' . $this->generateMediaTypeString($acceptType),
            ]
        );

        switch ($method) {
            case 'PUBLISH': $method = 'POST'; $headers[] = 'X-HTTP-Method-Override: PUBLISH'; break;
            case 'MOVE':    $method = 'POST'; $headers[] = 'X-HTTP-Method-Override: MOVE'; break;
            case 'PATCH':   $method = 'PATCH'; $headers[] = 'X-HTTP-Method-Override: PATCH'; break;
            case 'COPY':    $method = 'POST'; $headers[] = 'X-HTTP-Method-Override: COPY'; break;
        }

        $request = new HttpRequest($method, $uri, $this->httpHost);
        $request->addHeaders($headers);

        return $request;
    }

    protected function assertHttpResponseCodeEquals(HttpResponse $response, $expected)
    {
        $responseCode = $response->getStatusCode();
        if ($responseCode != $expected) {
            $errorMessageString = '';
            if (strpos($response->getHeader('Content-Type'), 'application/vnd.ez.api.ErrorMessage+xml') !== false) {
                $body = \simplexml_load_string($response->getContent());
                $errorMessageString = $this->getHttpResponseCodeErrorMessage($body);
            } elseif (strpos($response->getHeader('Content-Type'), 'application/vnd.ez.api.ErrorMessage+json') !== false) {
                $body = json_decode($response->getContent());
                $errorMessageString = $this->getHttpResponseCodeErrorMessage($body->ErrorMessage);
            }

            self::assertEquals($expected, $responseCode, $errorMessageString);
        }
    }

    private function getHttpResponseCodeErrorMessage($errorMessage)
    {
        $errorMessageString = <<< EOF
Server error message ({$errorMessage->errorCode}): {$errorMessage->errorMessage}

{$errorMessage->errorDescription}

EOF;

        // If server is in debug mode it will return file, line and trace.
        if (!empty($errorMessage->file)) {
            $errorMessageString .= "\nIn {$errorMessage->file}:{$errorMessage->line}\n\n{$errorMessage->trace}";
        } else {
            $errorMessageString .= "\nIn \<no trace, debug disabled\>";
        }

        return $errorMessageString;
    }

    protected function assertHttpResponseHasHeader(HttpResponse $response, $header, $expectedValue = null)
    {
        $headerValue = $response->getHeader($header);
        self::assertNotNull($headerValue, "Failed asserting that response has a $header header");
        if ($expectedValue !== null) {
            self::assertEquals($expectedValue, $headerValue);
        }
    }

    protected function generateMediaTypeString($typeString)
    {
        return "application/vnd.ez.api.$typeString";
    }

    protected function getMediaFromTypeString($typeString)
    {
        $prefix = 'application/vnd.ez.api.';
        self::assertStringStartsWith(
            $prefix,
            $typeString,
            "Unknown media: {$typeString}"
        );

        return substr($typeString, strlen($prefix));
    }

    protected function addCreatedElement($href)
    {
        $testCase = $this;
        self::$createdContent[$href] = function () use ($href, $testCase) {
            $testCase->sendHttpRequest(
                $testCase->createHttpRequest('DELETE', $href)
            );
        };
    }

    public static function tearDownAfterClass()
    {
        self::clearCreatedElement(self::$createdContent);
    }

    private static function clearCreatedElement(array $contentArray)
    {
        foreach (array_reverse($contentArray) as $href => $callback) {
            $callback();
        }
    }

    /**
     * @param string $parentLocationId The REST id of the parent location
     *
     * @return array created Content, as an array
     */
    protected function createFolder($string, $parentLocationId)
    {
        $string = $this->addTestSuffix($string);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="/api/ezp/v2/content/types/1" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="{$parentLocationId}" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ezp/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>{$string}</remoteId>
  <User href="/api/ezp/v2/user/users/14" />
  <modificationDate>2012-09-30T12:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$string}</fieldValue>
    </field>
  </fields>
</ContentCreate>
XML;

        return $this->createContent($xml);
    }

    /**
     * @param $xml
     *
     * @return array Content key of the Content struct array
     */
    protected function createContent($xml)
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/content/objects', 'ContentCreate+xml', 'Content+json');
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);

        $content = json_decode($response->getContent(), true);

        if (!isset($content['Content']['CurrentVersion']['Version'])) {
            self::fail("Incomplete response (no version):\n" . $response->getContent() . "\n");
        }

        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('PUBLISH', $content['Content']['CurrentVersion']['Version']['_href'])
        );

        self::assertHttpResponseCodeEquals($response, 204);

        $this->addCreatedElement($content['Content']['_href'], true);

        return $content['Content'];
    }

    /**
     * @param string $contentHref
     *
     * @return array
     */
    protected function getContentLocations($contentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentHref/locations", '', 'LocationList+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);
        $folderLocations = json_decode($response->getContent(), true);

        return $folderLocations;
    }

    protected function addTestSuffix($string)
    {
        if (!isset(self::$testSuffix)) {
            self::$testSuffix = uniqid();
        }

        return $string . '_' . self::$testSuffix;
    }

    /**
     * Sends a login request to the REST server.
     *
     * @return \stdClass an object with the name, identifier, csrftoken properties.
     */
    protected function login()
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/user/sessions', 'SessionInput+json', 'Session+json');
        $this->setSessionInput($request);
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 201);

        return json_decode($response->getContent())->Session;
    }

    /**
     * Sets the request's content to a JSON session creation payload.
     *
     * @param HttpRequest $request
     * @param string $password The password to use in the input. Will use the default one if not set.
     *
     * @return string
     */
    protected function setSessionInput(HttpRequest $request, $password = null)
    {
        $request->setContent(
            sprintf('{"SessionInput": {"login": "admin", "password": "%s"}}', $password ?: $this->loginPassword)
        );
    }
}
