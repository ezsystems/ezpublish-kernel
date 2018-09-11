<?php

/**
 * File containing the Functional\LocationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class LocationTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /content/objects/{contentId}/locations.
     * @returns string location href
     */
    public function testCreateLocation()
    {
        $content = $this->createFolder('testCreateLocation', '/api/ezp/v2/content/locations/1/2');
        $contentHref = $content['_href'];

        $remoteId = $this->addTestSuffix('testCreatelocation');

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<LocationCreate>
  <ParentLocation href="/api/ezp/v2/content/locations/1/43" />
  <remoteId>{$remoteId}</remoteId>
  <priority>0</priority>
  <hidden>false</hidden>
  <sortField>PATH</sortField>
  <sortOrder>ASC</sortOrder>
</LocationCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            "$contentHref/locations",
            'LocationCreate+xml',
            'Location+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations?remoteId=<locationRemoteId>
     */
    public function testRedirectLocationByRemoteId($locationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/locations?remoteId=' . $this->addTestSuffix('testCreateLocation'))
        );

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertHttpResponseHasHeader($response, 'Location', $locationHref);
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations?id=<locationId>
     */
    public function testRedirectLocationById($locationHref)
    {
        $hrefParts = explode('/', $locationHref);
        $id = array_pop($hrefParts);
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ezp/v2/content/locations?id=$id")
        );

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertHttpResponseHasHeader($response, 'Location', $locationHref);
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations?urlAlias=<Path/To-Content>
     */
    public function testRedirectLocationByURLAlias($locationHref)
    {
        $testUrlAlias = 'firstPart/secondPart/testUrlAlias';
        $this->createUrlAlias($locationHref, $testUrlAlias);

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ezp/v2/content/locations?urlAlias={$testUrlAlias}")
        );

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertHttpResponseHasHeader($response, 'Location', $locationHref);
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations/{locationPath}
     */
    public function testLoadLocation($locationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $locationHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateLocation
     * Covers COPY /content/locations/{locationPath}
     *
     * @return string the created location's href
     */
    public function testCopySubtree($locationHref)
    {
        $request = $this->createHttpRequest(
            'COPY',
            $locationHref,
            '',
            '',
            '',
            ['Destination' => '/api/ezp/v2/content/locations/1/43']
        );
        $response = $this->sendHttpRequest($request);
        $this->addCreatedElement($response->getHeaderLine('Location'));

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * Covers MOVE /content/locations/{locationPath}.
     * @depends testCopySubtree
     */
    public function testMoveSubtree($locationHref)
    {
        $request = $this->createHttpRequest(
            'MOVE',
            $locationHref,
            '',
            '',
            '',
            ['Destination' => '/api/ezp/v2/content/locations/1/5']
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/objects/{contentId}/locations
     */
    public function testLoadLocationsForContent($contentHref)
    {
    }

    /**
     * @depends testCreateLocation
     * Covers SWAP /content/locations/{locationPath}
     */
    public function testSwapLocation($locationHref)
    {
        self::markTestSkipped('@todo Implement');

        /*$content = $this->createFolder( __FUNCTION__, "/api/ezp/v2/content/locations/1/2" );

        $request = $this->createHttpRequest( 'SWAP', $locationHref );
        $request->addHeader( "Destination: $newFolderHref" );

        $response = $this->sendHttpRequest( $request );
        self::assertHttpResponseCodeEquals( $response, 204 );*/
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations/{locationPath}/children
     */
    public function testLoadLocationChildren($locationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$locationHref/children", '', 'LocationList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);
        self::assertHttpResponseHasHeader($response, 'Content-Type', $this->generateMediaTypeString('LocationList+json'));
    }

    /**
     * Covers PATCH /content/locations/{locationPath}.
     * @depends testCreateLocation
     */
    public function testUpdateLocation($locationHref)
    {
        $body = <<< XML
<LocationUpdate>
  <priority>3</priority>
  <sortField>PATH</sortField>
  <sortOrder>ASC</sortOrder>
</LocationUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $locationHref,
            'LocationUpdate+xml',
            'Location+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateLocation
     * Covers DELETE /content/locations/{path}
     */
    public function testDeleteSubtree($locationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $locationHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    private function createUrlAlias(string $locationHref, string $urlAlias): string
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UrlAliasCreate type="LOCATION">
  <location href="{$locationHref}" />
  <path>/{$urlAlias}</path>
  <languageCode>eng-GB</languageCode>
  <alwaysAvailable>false</alwaysAvailable>
  <forward>true</forward>
</UrlAliasCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/urlaliases',
            'UrlAliasCreate+xml',
            'UrlAlias+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseHasHeader($response, 'Location');
        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }
}
