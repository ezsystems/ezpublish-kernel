<?php

/**
 * File containing the Functional\BinaryContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\SPI\Variation\Values\ImageVariation;

class BinaryContentTest extends RESTFunctionalTestCase
{
    const IMAGE_FILE = __DIR__ . '/../../../../Publish/API/Repository/Tests/FieldType/_fixtures/image.jpg';

    public function testGetImageVariation()
    {
        $imageArray = $this->createImage();
        $variationHref = $imageArray['CurrentVersion']['Version']['Fields']['field'][1]['fieldValue']['variations']['large']['href'];

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $variationHref, '', 'ContentImageVariation+json')
        );

        $this->assertHttpResponseCodeEquals($response, 200);
        $this->assertHttpResponseHasCacheTags(
            $response,
            [
                'content-' . $imageArray['_id'],
                'content-type-5',
                // we can't test the location, as we don't have it. Test it without the id ?
            ]
        );
    }

    private function createImage()
    {
        $name = uniqid('createImage');
        $remoteId = md5($name);
        $imageBase64 = base64_encode(file_get_contents(self::IMAGE_FILE));
        $fileSize = filesize(self::IMAGE_FILE);

        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="/api/ezp/v2/content/types/5" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ezp/v2/content/locations/1/2" />
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
      <fieldValue>{$name}</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>image</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>
        <value key="fileName">test-image.jpg</value>
        <value key="fileSize">{$fileSize}</value>
        <value key="alternativeText">Test</value>
        <value key="data"><![CDATA[{$imageBase64}]]></value>
      </fieldValue>
    </field>
  </fields>
</ContentCreate>
XML;
        return $this->createContent($xml);
    }
}
