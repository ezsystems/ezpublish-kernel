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
        $dataMap = [
            1 => 'quick',
            2 => 'brown',
            3 => 'fox',
            4 => 'news',
            5 => 'quick brown',
            6 => 'quick fox',
            7 => 'quick news',
            8 => 'brown fox',
            9 => 'brown news',
            10 => 'fox news',
            11 => 'quick brown fox',
            12 => 'quick brown news',
            13 => 'quick fox news',
            14 => 'brown fox news',
            15 => 'quick brown fox news',
        ];

        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $idMap = [];

        foreach ($dataMap as $key => $string) {
            $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
            $contentCreateStruct->setField('name', $string);

            $content = $contentService->publishVersion(
                $contentService->createContent(
                    $contentCreateStruct,
                    [$locationService->newLocationCreateStruct(2)]
                )->versionInfo
            );

            $idMap[$key] = $content->id;
        }

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
    public function providerForTestFulltextSearchSolr6(): array
    {
        return [
            [
                'fox',
                [3, [6, 8, 10], [11, 13, 14, 15]],
            ],
            [
                'quick fox',
                $quickOrFox = [6, [11, 13, 15], [1, 3], [5, 7, 8, 10], [12, 14]],
            ],
            [
                'quick OR fox',
                $quickOrFox,
            ],
            [
                'quick AND () OR AND fox',
                $quickOrFox,
            ],
            [
                '+quick +fox',
                $quickAndFox = [6, [11, 13, 15]],
            ],
            [
                'quick AND fox',
                $quickAndFox,
            ],
            [
                'brown +fox -news',
                [8, 11, 3, 6],
            ],
            [
                'quick +fox -news',
                [6, 11, 3, 8],
            ],
            [
                'quick brown +fox -news',
                $notNewsFox = [11, [6, 8], 3],
            ],
            [
                '((quick AND fox) OR (brown AND fox) OR fox) AND NOT news',
                $notNewsFox,
            ],
            [
                '"quick brown"',
                [5, [11, 12, 15]],
            ],
            [
                '"quick brown" AND fox',
                [[11, 15]],
            ],
            [
                'quick OR brown AND fox AND NOT news',
                [11, 8],
            ],
            [
                '(quick OR brown) AND fox AND NOT news',
                [11, [6, 8]],
            ],
            [
                '"fox brown"',
                [],
            ],
            [
                'qui*',
                [[1, 5, 6, 7, 11, 12, 13, 15]],
            ],
            [
                '+qui* +fox',
                [6, [11, 13, 15]],
            ],
        ];
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
                'fox',
                [3, [6, 8, 10], [11, 13, 14], 15],
            ],
            [
                'quick fox',
                $quickOrFox = [6, [11, 13], 15, [1, 3], [5, 7, 8, 10], [12, 14]],
            ],
            [
                'quick OR fox',
                $quickOrFox,
            ],
            [
                'quick AND () OR AND fox',
                $quickOrFox,
            ],
            [
                '+quick +fox',
                $quickAndFox = [6, [11, 13], 15],
            ],
            [
                'quick AND fox',
                $quickAndFox,
            ],
            [
                'brown +fox -news',
                [8, 11, 3, 6],
            ],
            [
                'quick +fox -news',
                [6, 11, 3, 8],
            ],
            [
                'quick brown +fox -news',
                $notNewsFox = [11, [6, 8], 3],
            ],
            [
                '((quick AND fox) OR (brown AND fox) OR fox) AND NOT news',
                $notNewsFox,
            ],
            [
                '"quick brown"',
                [5, [11, 12], 15],
            ],
            [
                '"quick brown" AND fox',
                [11, 15],
            ],
            [
                'quick OR brown AND fox AND NOT news',
                [11, 8],
            ],
            [
                '(quick OR brown) AND fox AND NOT news',
                [11, [6, 8]],
            ],
            [
                '"fox brown"',
                [],
            ],
            [
                'qui*',
                [[1, 5, 6, 7, 11, 12, 13, 15]],
            ],
            [
                '+qui* +fox',
                [6, [11, 13], 15],
            ],
        ];
    }

    /**
     * Test for the findContent() method on Solr 6.
     *
     * @param string $searchString
     * @param array $expectedKeys
     * @param array $idMap
     *
     * @depends testPrepareContent
     * @dataProvider providerForTestFulltextSearchSolr6
     */
    public function testFulltextContentSearchSolr6(string $searchString, array $expectedKeys, array $idMap): void
    {
        if (($solrVersion = getenv('SOLR_VERSION')) >= 7) {
            $this->markTestSkipped('This test is only relevant for Solr 6');
        }

        $this->doTestFulltextContentSearch($searchString, $expectedKeys, $idMap);
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
     * Test for the findLocations() method on Solr 6.
     *
     * @param $searchString
     * @param array $expectedKeys
     * @param array $idMap
     *
     * @depends testPrepareContent
     * @dataProvider providerForTestFulltextSearchSolr6
     */
    public function testFulltextLocationSearchSolr6($searchString, array $expectedKeys, array $idMap): void
    {
        if (!$this->isSolrMajorVersionInRange('6.0.0', '7.0.0')) {
            $this->markTestSkipped('This test is only relevant for Solr 6');
        }

        $this->doTestFulltextLocationSearch($searchString, $expectedKeys, $idMap);
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

    /**
     * Checks if Solr version is in the given range.
     *
     * @param string $minVersion
     * @param string $maxVersion
     *
     * @return bool
     */
    private function isSolrMajorVersionInRange(string $minVersion, string $maxVersion): bool
    {
        $version = getenv('SOLR_VERSION');
        if (is_string($version) && !empty($version)) {
            return version_compare($version, $minVersion, '>=') && version_compare($version, $maxVersion, '<');
        }

        return false;
    }
}
