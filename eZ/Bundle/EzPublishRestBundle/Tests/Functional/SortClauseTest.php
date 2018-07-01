<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use SimpleXMLElement;

class SortClauseTest extends RESTFunctionalTestCase
{
    public function testFieldSortClause()
    {
        $string = $this->addTestSuffix(__FUNCTION__);
        $mainTestFolderContent = $this->createFolder($string, '/api/ezp/v2/content/locations/1/2');

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $mainTestFolderContent['_href'], '', 'Content+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $mainFolderContent = json_decode($response->getBody(), true);

        if (!isset($mainFolderContent['Content']['MainLocation']['_href'])) {
            self::fail("Incomplete response (no main location):\n" . $response->getBody() . "\n");
        }

        $mainFolderLocationHref = $mainFolderContent['Content']['MainLocation']['_href'];

        $locationArray = explode('/', $mainFolderLocationHref);
        $mainFolderLocationId = array_pop($locationArray);

        $foldersForSorting = [
            'AAA',
            'BBB',
            'CCC',
        ];

        $foldersNames = [];

        foreach ($foldersForSorting as $folder) {
            $folderContent = $this->createFolder($folder, $mainFolderLocationHref);
            $foldersNames[$folder] = $folderContent['Name'];
        }

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>TestView</identifier>
  <LocationQuery>
    <Filter>
      <ParentLocationIdCriterion>{$mainFolderLocationId}</ParentLocationIdCriterion>
    </Filter>
    <limit>10</limit>
    <offset>0</offset>
    <SortClauses>
      <Field identifier="folder/name">descending</Field>
    </SortClauses>
  </LocationQuery>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/views',
            'ViewInput+xml; version=1.1',
            'View+xml',
            $body
        );

        $response = $this->sendHttpRequest(
            $request
        );

        self::assertHttpResponseCodeEquals($response, 200);
        $xml = new SimpleXMLElement($response->getBody());

        $searchHits = [];
        foreach ($xml->xpath('//Name') as $searchHit) {
            $searchHits[] = (string) $searchHit[0];
        }

        self::assertCount(3, $searchHits);
        self::assertEquals($foldersNames['CCC'], $searchHits[0]);
        self::assertEquals($foldersNames['BBB'], $searchHits[1]);
        self::assertEquals($foldersNames['AAA'], $searchHits[2]);
    }
}
