<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\FieldType\RichText\Value as RichTextValue;

use RuntimeException;

/**
 * Test case for full text search in the SearchService.
 *
 * @see \eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 * @group fulltext
 */
class SearchServiceFulltextTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();

        if (
        !$this
            ->getRepository(false)
            ->getSearchService()->supports(SearchService::CAPABILITY_ADVANCED_FULLTEXT)
        ) {
            $this->markTestSkipped('Engine says it does not support advance fulltext format');
        }
    }

    /**
     * Create test Content and return Content ID map for subsequent testing.
     */
    public function testPrepareContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('article');
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreateStruct->setField('title', 'testtt');

        $bodyDocument = new \DOMDocument();
        $bodyDocument->loadXML(<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
<para>​<ezembedinline xlink:href="ezcontent://57" view="embed-inline"/></para>
</section>
EOT
        );

        $introDocument = new \DOMDocument();
        $introDocument->loadXML(<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
<para>paragraph</para>
</section>
EOT
        );

        $contentCreateStruct->setField('body', new RichTextValue($bodyDocument), 'eng-GB');
        $contentCreateStruct->setField('intro', new RichTextValue($introDocument), 'eng-GB');

        $idMap = [];

        $content = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                [$locationService->newLocationCreateStruct(2)]
            )->versionInfo
        );

        $idMap[1] = $content->id;

// SECOND ARTICLE

        $contentType = $contentTypeService->loadContentTypeByIdentifier('article');
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreateStruct->setField('title', 'testtt111');

        $bodyDocument = new \DOMDocument();
        $bodyDocument->loadXML(<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
<para>​body-content</para>
</section>
EOT
        );

        $introDocument = new \DOMDocument();
        $introDocument->loadXML(<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
<para>paragraph 2</para>
</section>
EOT
        );

        $contentCreateStruct->setField('body', new RichTextValue($bodyDocument), 'eng-GB');
        $contentCreateStruct->setField('intro', new RichTextValue($introDocument), 'eng-GB');

        $content = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                [$locationService->newLocationCreateStruct(2)]
            )->versionInfo
        );

        $idMap[2] = $content->id;


        $this->refreshSearch($repository);

        return $idMap;
    }

    /**
     * Return pairs of arguments:
     *  - search string for testing
     *  - an array of corresponding Content keys as defined in testPrepareContent() method,
     *    ordered and grouped by relevancy.
     *
     * @see testPrepareContent
     */
    public function providerForTestFulltextSearchSolr7(): array
    {
        return [
            [
                'test',
                [3, [6, 8, 10], [11, 13, 14], 15],
            ],
        ];
    }

    /**
     * Test for the findContent() method on Solr >= 7.
     *
     * @param string $searchString
     * @param array $expectedKeys
     * @param array $idMap
     *
     * @depends testPrepareContent
     * @dataProvider providerForTestFulltextSearchSolr7
     */
    public function testFulltextContentSearchSolr7(string $searchString, array $expectedKeys, array $idMap): void
    {
        if (($solrVersion = getenv('SOLR_VERSION')) < 7) {
            $this->markTestSkipped('This test is only relevant for Solr >= 7');
        }

        $this->doTestFulltextContentSearch($searchString, $expectedKeys, $idMap);
    }

    private function doTestFulltextContentSearch(string $searchString, array $expectedKeys, array $idMap): void
    {
        $repository = $this->getRepository(false);
        $searchService = $repository->getSearchService();

        $query = new Query(['query' => new Criterion\FullText($searchString)]);
        $searchResult = $searchService->findContent($query);

        $this->assertFulltextSearch($searchResult, $expectedKeys, $idMap);
    }

    /**
     * Test for the findLocations() method on Solr >= 7.
     *
     * @param $searchString
     * @param array $expectedKeys
     * @param array $idMap
     *
     * @depends testPrepareContent
     * @dataProvider providerForTestFulltextSearchSolr7
     */
    public function testFulltextLocationSearchSolr7($searchString, array $expectedKeys, array $idMap): void
    {
        if (($solrVersion = getenv('SOLR_VERSION')) < 7) {
            $this->markTestSkipped('This test is only relevant for Solr >= 7');
        }

        $this->doTestFulltextLocationSearch($searchString, $expectedKeys, $idMap);
    }

    private function doTestFulltextLocationSearch($searchString, array $expectedKeys, array $idMap): void
    {
        $repository = $this->getRepository(false);
        $searchService = $repository->getSearchService();

        $query = new LocationQuery(['query' => new Criterion\FullText($searchString)]);
        $searchResult = $searchService->findLocations($query);

        $this->assertFulltextSearch($searchResult, $expectedKeys, $idMap);
    }

    /**
     * Assert given $searchResult using $expectedKeys and $idMap.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     * @param array $expectedKeys
     * @param array $idMap
     */
    public function assertFulltextSearch(SearchResult $searchResult, array $expectedKeys, array $idMap)
    {
        $this->assertEquals(
            array_reduce(
                $expectedKeys,
                function ($carry, $item) {
                    $carry += count((array)$item);

                    return $carry;
                },
                0
            ),
            $searchResult->totalCount
        );

        $expectedIds = $this->mapKeysToIds($expectedKeys, $idMap);
        $actualIds = $this->mapSearchResultToIds($searchResult);

        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * Map given array of $expectedKeys to Content IDs, using $idMap.
     *
     * @param array $expectedKeys
     * @param array $idMap
     *
     * @return array
     */
    private function mapKeysToIds(array $expectedKeys, array $idMap)
    {
        $expectedIds = [];

        foreach ($expectedKeys as $keyGroup) {
            if (is_array($keyGroup)) {
                $idGroup = [];

                /** @var array $keyGroup */
                foreach ($keyGroup as $key) {
                    $idGroup[] = $idMap[$key];
                }

                sort($idGroup);
                $expectedIds[] = $idGroup;

                continue;
            }

            $key = $keyGroup;
            $expectedIds[] = $idMap[$key];
        }

        return $expectedIds;
    }

    /**
     * Map given $searchResult to an array of Content IDs, ordered and grouped by relevancy score.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array
     */
    private function mapSearchResultToIds(SearchResult $searchResult)
    {
        $scoreGroupedIds = [];

        foreach ($searchResult->searchHits as $index => $searchHit) {
            if ($searchHit->valueObject instanceof Content || $searchHit->valueObject instanceof Location) {
                $contentInfo = $searchHit->valueObject->contentInfo;
            } elseif ($searchHit->valueObject instanceof ContentInfo) {
                $contentInfo = $searchHit->valueObject;
            } else {
                throw new RuntimeException('Unknown search hit value');
            }

            $scoreGroupedIds[(string)$searchHit->score][] = $contentInfo->id;
        }

        return array_map(
            function (array $idGroup) {
                if (count($idGroup) === 1) {
                    return reset($idGroup);
                }

                sort($idGroup);

                return $idGroup;
            },
            array_values($scoreGroupedIds)
        );
    }
}
