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
    /**
     * @dataProvider sortingClauseDataProvider
     *
     * @param array $foldersNameToCreate
     * @param string $sortClauseXML
     * @param array $foldersInExpectedOrder
     *
     * @throws \Psr\Http\Client\ClientException
     */
    public function testFieldSortClause(array $foldersNameToCreate, string $sortClauseXML, array $foldersInExpectedOrder)
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

        $foldersNames = [];
        foreach ($foldersNameToCreate as $folder) {
            $folderContent = $this->createFolder($folder, $mainFolderLocationHref);
            $foldersNames[$folder] = $folderContent['Name'];
        }

        $sortedFoldersNames = [];
        foreach ($foldersInExpectedOrder as $name) {
            $sortedFoldersNames[] = $foldersNames[$name];
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
        $sortClauseXML
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

        $expectedCount = count($foldersInExpectedOrder);
        self::assertCount($expectedCount, $searchHits);

        for ($i = 0; $i <= $expectedCount - 1; ++$i) {
            self::assertEquals($sortedFoldersNames[$i], $searchHits[$i]);
        }
    }

    public function sortingClauseDataProvider()
    {
        return [
            [
                [
                    'AAA',
                    'BBB',
                    'CCC',
                ],
                '<Field identifier="folder/name">descending</Field>',
                [
                    'CCC',
                    'BBB',
                    'AAA',
                ],
            ],
            [
                [
                    'This',
                    'Is Not',
                    'Alphabetical',
                ],
                '<LocationId>descending</LocationId>',
                [
                    'Alphabetical',
                    'Is Not',
                    'This',
                ],
            ],
        ];
    }
}
