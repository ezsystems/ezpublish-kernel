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

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class UrlAliasTest extends RESTFunctionalTestCase
{
    /**
     * @covers nothing. Creates a folder for other tests.
     *
     * @return string The folder's main location href
     */
    public function testCreateFolder()
    {
        $folderArray = $this->createFolder(__METHOD__, '/api/ezp/v2/content/locations/1/2');
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
        self::markTestSkipped('@todo fixme');

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $urlAliasHref)
        );

        // @todo Will fail because of EZP-21082
        // self::assertHttpResponseCodeEquals( $response, 200 );
        self::assertHttpResponseCodeEquals($response, 500);
        self::markTestSkipped('@todo Fix when EZP-21082 is fixed');
    }

    /**
     * @depends testCreateUrlAlias
     * @covers DELETE /content/urlaliases/{urlAliasId}
     */
    public function testDeleteURLAlias($urlAliasHref)
    {
        self::markTestSkipped('@todo fixme');

        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('DELETE', $urlAliasHref)
        );

        // @todo will fail because of EZP-21082
        // self::assertHttpResponseCodeEquals( $response, 204 );
        self::assertHttpResponseCodeEquals($response, 500);

        self::markTestSkipped('@todo Fix when EZP-21082 is fixed');
    }

    /**
     * @depends testCreateFolder
     * @covers GET /content/locations/{locationPath}/urlaliases
     */
    public function testListLocationURLAliases($contentLocationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentLocationHref/urlaliases")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }
}
