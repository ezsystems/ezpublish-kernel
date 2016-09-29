<?php

/**
 * File containing the Functional\UrlAliasTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use Buzz\Message\Response;
use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Server\Values\URLAliasRefList;

class UrlAliasTest extends RESTFunctionalTestCase
{
    /**
     * @covers nothing. Creates a folder for other tests.
     *
     * @return string The folder's main location href
     */
    public function testCreateFolder()
    {
        $folderArray = $this->createFolder(__FUNCTION__, '/api/ezp/v2/content/locations/1/2');
        $folderLocations = $this->getContentLocations($folderArray['_href']);

        return $folderLocations['LocationList']['Location'][0]['_href'];
    }

    /**
     * @covers GET /content/urlaliases
     */
    public function testListGlobalURLAliases()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/urlaliases')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateFolder
     * @covers POST /content/urlaliases
     * @returns string The created url alias href
     */
    public function testCreateUrlAlias($locationHref)
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UrlAliasCreate type="LOCATION">
  <location href="{$locationHref}" />
  <path>/{$text}</path>
  <languageCode>eng-GB</languageCode>
  <alwaysAvailable>false</alwaysAvailable>
  <forward>true</forward>
</UrlAliasCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/urlaliases',
            'UrlAliasCreate+xml',
            'UrlAlias+json'
        );
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @covers POST /content/urlaliases
     * @returns string The created url alias href
     */
    public function testCreateGlobalUrlAlias()
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UrlAliasCreate type="RESOURCE">
  <resource>module:/content/search</resource>
  <path>/$text</path>
  <languageCode>eng-GB</languageCode>
  <alwaysAvailable>false</alwaysAvailable>
  <forward>true</forward>
</UrlAliasCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/urlaliases',
            'UrlAliasCreate+xml',
            'UrlAlias+json'
        );
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateUrlAlias
     * @covers GET /content/urlaliases/{urlAliasId}
     */
    public function testLoadURLAlias($urlAliasHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $urlAliasHref, '', 'UrlAlias+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $urlAlias = $this->parseUrlAliasFromResponse($response);
        $this->assertHttpResponseHasCacheTags(
            $response,
            [
                'location-' . $urlAlias->destination->id,
            ]
        );
    }

    /**
     * @depends testCreateFolder
     * @covers GET /content/locations/{locationPath}/urlaliases
     */
    public function testListLocationURLAliases($contentLocationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentLocationHref/urlaliases", '', 'UrlAliasRefList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $urlAliasList = $this->parseUrlAliasRefListFromResponse($response);
        $this->assertEquals(1, count($urlAliasList));
        $this->assertHttpResponseHasCacheTags(
            $response,
            [
                'location-' . $this->extractLocationIdFromHref($contentLocationHref),
            ]
        );
    }

    /**
     * @depends testCreateUrlAlias
     * @covers DELETE /content/urlaliases/{urlAliasId}
     */
    public function testDeleteURLAlias($urlAliasHref)
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('DELETE', $urlAliasHref)
        );

        self::assertHttpResponseCodeEquals( $response, 204 );
    }

    /**
     * @param Response $response
     * @return URLAliasRefList
     */
    private function parseUrlAliasRefListFromResponse(Response $response)
    {
        $responseStruct = json_decode($response->getContent(), true);

        return new URLAliasRefList(
            [
                'urlaliases' => array_map(
                    function (array $urlAliasRefRow) {
                        return new URLAlias(
                            [
                                'id' => $this->extractLastIdFromHref($urlAliasRefRow['_href']),
                            ]
                        );
                    },
                    $responseStruct['UrlAliasRefList']['UrlAlias']
                )
            ],
            ''
        );
    }

    /**
     * Extracts a location id from any location path href.
     * @param string $href Ex: /api/ezp/v2/content/locations/1/2/3
     * @return int
     */
    private function extractLocationIdFromHref($href)
    {
        // iterates over href parts, and returns the last numeric part before a non numeric part
        foreach (explode('/', $href) as $part) {
            if (is_numeric($part)) {
                $id = $part;
            } else if (isset($id)) {
                return $id;
            }
        }
    }

    /**
     * @param Response $response
     * @return URLAlias
     */
    private function parseUrlAliasFromResponse(Response $response)
    {
        $responseStruct = json_decode($response->getContent(), true);

        return new URLAlias(
            [
                'id' => $responseStruct['UrlAlias']['_id'],
                'destination' => new Location(['id' => $this->extractLastIdFromHref($responseStruct['UrlAlias']['location']['_href'])]),
            ]
        );
    }
}
