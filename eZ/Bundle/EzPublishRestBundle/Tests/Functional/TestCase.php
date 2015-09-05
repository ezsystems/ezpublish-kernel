<?php

/**
 * File containing the Functional\TestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use Buzz\Message\Request as HttpRequest;
use Buzz\Message\Response as HttpResponse;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        parent::setUp();

        $this->httpHost = getenv('EZP_TEST_REST_HOST') ?: 'localhost';
        $this->httpAuth = getenv('EZP_TEST_REST_AUTH') ?: 'admin:publish';

        $this->httpClient = new \Buzz\Client\Curl();
        $this->httpClient->setVerifyPeer(false);
        $this->httpClient->setTimeout(90);
        $this->httpClient->setOption(CURLOPT_FOLLOWLOCATION, false);
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

    /**
     * @return HttpRequest
     */
    public function createHttpRequest($method, $uri, $contentType = '', $acceptType = '')
    {
        $request = new HttpRequest($method, $uri, $this->httpHost);
        $request->addHeader('Authorization: Basic ' . base64_encode($this->httpAuth));
        $request->addHeader('Content-Type: ' . $this->generateMediaTypeString($contentType));
        $request->addHeader('Accept: ' . $this->generateMediaTypeString($acceptType));

        return $request;
    }

    protected function assertHttpResponseCodeEquals(HttpResponse $response, $expected)
    {
        $responseCode = $response->getStatusCode();
        if ($responseCode != $expected) {
            $errorMessageString = '';
            if ($response->getHeader('Content-Type') == 'application/vnd.ez.api.ErrorMessage+xml') {
                $body = \simplexml_load_string($response->getContent());
                $errorMessageString = $body->errorDescription;
            } elseif (($response->getHeader('Content-Type') == 'application/vnd.ez.api.ErrorMessage+json')) {
                $body = json_decode($response->getContent());
                $errorMessageString = "Error message: {$body->ErrorMessage->errorDescription}";
            }

            self::assertEquals($expected, $responseCode, $errorMessageString);
        }
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
     * List of REST contentId (/content/objects/12345) created by tests.
     *
     * @var array
     */
    private static $createdContent = array();
}
