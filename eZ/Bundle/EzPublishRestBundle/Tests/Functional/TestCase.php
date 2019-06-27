<?php

/**
 * File containing the Functional\TestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use Buzz\Browser;
use Buzz\Client\BuzzClientInterface;
use Buzz\Client\Curl;
use Nyholm\Psr7\Factory\Psr17Factory as HttpFactory;
use Nyholm\Psr7\Request as HttpRequest;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class TestCase extends BaseTestCase
{
    const X_HTTP_METHOD_OVERRIDE_MAP = [
        'PUBLISH' => 'POST',
        'MOVE' => 'POST',
        'PATCH' => 'PATCH',
        'COPY' => 'POST',
    ];

    /** @var \Buzz\Client\BuzzClientInterface */
    private $httpClient;

    /** @var string */
    private $httpHost;

    /**
     * @var string
     * Basic auth login:password
     */
    private $httpAuth;

    protected static $testSuffix;

    /** @var array */
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
    private static $createdContent = [];

    protected function setUp()
    {
        parent::setUp();

        $this->httpHost = getenv('EZP_TEST_REST_HOST') ?: 'localhost';
        $this->httpAuth = getenv('EZP_TEST_REST_AUTH') ?: 'admin:publish';
        list($this->loginUsername, $this->loginPassword) = explode(':', $this->httpAuth);

        $this->httpClient = new Curl(
            [
                'verify' => false,
                'timeout' => 90,
                'allow_redirects' => false,
            ],
            new HttpFactory()
        );

        if ($this->autoLogin) {
            $session = $this->login();
            $this->headers['Cookie'] = sprintf('%s=%s', $session->name, $session->identifier);
            $this->headers['X-CSRF-Token'] = $session->csrfToken;
        }
    }

    /**
     * Instantiate Browser object.
     *
     * @return \Buzz\Client\BuzzClientInterface
     */
    public function createBrowser(): BuzzClientInterface
    {
        if ($this->httpClient === null) {
            throw new RuntimeException('Unable to create browser - test is not initialized');
        }

        return new Browser($this->httpClient, new HttpFactory());
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Psr\Http\Client\ClientException
     */
    public function sendHttpRequest(RequestInterface $request): ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
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
     * Get base URI for \Buzz\Browser based requests.
     *
     * @return string
     */
    protected function getBaseURI()
    {
        return "http://{$this->httpHost}";
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $contentType
     * @param string $acceptType
     * @param string $body
     *
     * @param array $extraHeaders [key => value] array of extra headers
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createHttpRequest(
        string $method,
        string $uri,
        string $contentType = '',
        string $acceptType = '',
        string $body = '',
        array $extraHeaders = []
    ): RequestInterface {
        $headers = array_merge(
            $method === 'POST' && $uri === '/api/ezp/v2/user/sessions' ? [] : $this->headers,
            [
                'Content-Type' => $this->generateMediaTypeString($contentType),
                'Accept' => $this->generateMediaTypeString($acceptType),
            ]
        );

        if (isset(static::X_HTTP_METHOD_OVERRIDE_MAP[$method])) {
            $headers['X-HTTP-Method-Override'] = $method;
            $method = static::X_HTTP_METHOD_OVERRIDE_MAP[$method];
        }

        return new HttpRequest(
            $method,
            $this->getBaseURI() . $uri,
            array_merge($headers, $extraHeaders),
            $body
        );
    }

    protected function assertHttpResponseCodeEquals(ResponseInterface $response, $expected)
    {
        $responseCode = $response->getStatusCode();
        try {
            self::assertEquals($expected, $responseCode);
        } catch (ExpectationFailedException $e) {
            $errorMessageString = '';
            $contentTypeHeader = $response->hasHeader('Content-Type')
                ? $response->getHeader('Content-Type')[0]
                : '';

            if (strpos($contentTypeHeader, 'application/vnd.ez.api.ErrorMessage+xml') !== false) {
                $body = \simplexml_load_string($response->getBody());
                $errorMessageString = $this->getHttpResponseCodeErrorMessage($body);
            } elseif (strpos($contentTypeHeader, 'application/vnd.ez.api.ErrorMessage+json') !== false) {
                $body = json_decode($response->getBody());
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

    protected function assertHttpResponseHasHeader(ResponseInterface $response, $header, $expectedValue = null)
    {
        $headerValue = $response->hasHeader($header) ? $response->getHeader($header)[0] : null;
        self::assertNotNull($headerValue, "Failed asserting that response has a {$header} header");
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
     * @param string $string The value of the folders name field
     * @param string $parentLocationId The REST resource id of the parent location
     * @param string|null $remoteId
     *
     * @return array created Content, as an array
     */
    protected function createFolder(
        string $string,
        string $parentLocationId,
        ?string $remoteId = null
    ): array {
        $string = $this->addTestSuffix($string);
        $remoteId = $remoteId ?? md5(uniqid($string, true));
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
  <remoteId>{$remoteId}</remoteId>
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
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/objects',
            'ContentCreate+xml',
            'Content+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);

        $content = json_decode($response->getBody(), true);

        if (!isset($content['Content']['CurrentVersion']['Version'])) {
            self::fail("Incomplete response (no version):\n" . $response->getBody() . "\n");
        }

        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('PUBLISH', $content['Content']['CurrentVersion']['Version']['_href'])
        );

        self::assertHttpResponseCodeEquals($response, 204);

        $this->addCreatedElement($content['Content']['_href']);

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
        $folderLocations = json_decode($response->getBody(), true);

        return $folderLocations;
    }

    protected function addTestSuffix($string)
    {
        if (!isset(self::$testSuffix)) {
            /** @noinspection NonSecureUniqidUsageInspection */
            self::$testSuffix = uniqid('', true);
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
        $request = $this->createAuthenticationHttpRequest($this->getLoginUsername(), $this->getLoginPassword());
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 201);

        return json_decode($response->getBody())->Session;
    }

    /**
     * @param string $login
     * @param string $password
     * @param array $extraHeaders extra [key => value] headers to be passed with the authentication request.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function createAuthenticationHttpRequest(string $login, string $password, array $extraHeaders = [])
    {
        return $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/sessions',
            'SessionInput+json',
            'Session+json',
            sprintf('{"SessionInput": {"login": "%s", "password": "%s"}}', $login, $password),
            $extraHeaders
        );
    }
}
