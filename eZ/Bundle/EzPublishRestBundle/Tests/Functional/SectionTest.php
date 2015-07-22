<?php

/**
 * File containing the Functional\SectionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class SectionTest extends RESTFunctionalTestCase
{
    /**
     * @covers GET /content/sections
     */
    public function testListSections()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/sections')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers POST /content/sections
     *
     * @return string The created section href
     */
    public function testCreateSection()
    {
        $xml = <<< XML
<SectionInput>
  <identifier>testCreateSection</identifier>
  <name>testCreateSection</name>
</SectionInput>
XML;
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/content/sections', 'SectionInput+xml', 'Section+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @param $sectionHref
     * @depends testCreateSection
     * @covers PATCH /content/sections/{sectionId}
     */
    public function testUpdateSection($sectionHref)
    {
        $xml = <<< XML
<SectionInput>
  <identifier>testUpdateSection</identifier>
  <name>testUpdateSection</name>
</SectionInput>
XML;
        $request = $this->createHttpRequest('PATCH', $sectionHref, 'SectionInput+xml', 'Section+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers GET /content/sections/{sectionId}
     * @depends testCreateSection
     */
    public function testLoadSection($sectionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $sectionHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateSection
     * @covers GET /content/sections?identifier={sectionIdentifier}
     */
    public function testLoadSectionByIdentifier($sectionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/sections?identifier=testUpdateSection')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateSection
     * @covers DELETE /content/sections/{sectionId}
     */
    public function testDeleteSection($sectionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $sectionHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }
}
