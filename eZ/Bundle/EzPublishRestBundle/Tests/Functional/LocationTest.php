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
     * @covers POST /content/objects/{contentId}/locations
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
        $request = $this->createHttpRequest('POST', "$contentHref/locations", 'LocationCreate+xml', 'Location+json');
        $request->setContent($body);

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');

        return $href;
    }

    /**
     * @depends testCreateLocation
     * @covers GET /content/locations?remoteId=<locationRemoteId>
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
     * @covers GET /content/locations?id=<locationId>
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
     * @covers GET /content/locations/{locationPath}
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
     * @covers COPY /content/locations/{locationPath}
     *
     * @return string the created location's href
     */
    public function testCopySubtree($locationHref)
    {
        $request = $this->createHttpRequest('COPY', $locationHref);
        $request->addHeader('Destination: /api/ezp/v2/content/locations/1/43');
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location');
    }

    /**
     * @covers MOVE /content/locations/{locationPath}
     * @depends testCopySubtree
     */
    public function testMoveSubtree($locationHref)
    {
        $request = $this->createHttpRequest('MOVE', $locationHref);
        $request->addHeader('Destination: /api/ezp/v2/content/locations/1/5');
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    /**
     * @depends testCreateLocation
     * @covers GET /content/objects/{contentId}/locations
     */
    public function testLoadLocationsForContent($contentHref)
    {
    }

    /**
     * @depends testCreateLocation
     * @covers SWAP /content/locations/{locationPath}
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
     * @covers GET /content/locations/{locationPath}/children
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
     * @covers PATCH /content/locations/{locationPath}
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

        $request = $this->createHttpRequest('PATCH', $locationHref, 'LocationUpdate+xml', 'Location+json');
        $request->setContent($body);

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateLocation
     * @covers DELETE /content/locations/{path}
     */
    public function testDeleteSubtree($locationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $locationHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }
}
