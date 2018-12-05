<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Tests\Content;

use eZ\Publish\Core\Search\Legacy\Content;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler as CommonCriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\CriterionHandler as LocationCriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler as CommonSortClauseHandler;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\SortClauseHandler as LocationSortClauseHandler;
use eZ\Publish\Core\Search\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;

/**
 * Location Search test case for ContentSearchHandler.
 */
class HandlerLocationSortTest extends AbstractTestCase
{
    protected function getIds($searchResult)
    {
        $ids = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $searchResult->searchHits
        );

        return $ids;
    }

    /**
     * Returns the location search handler to test.
     *
     * This method returns a fully functional search handler to perform tests on.
     *
     * @return \eZ\Publish\Core\Search\Legacy\Content\Handler
     */
    protected function getContentSearchHandler()
    {
        return new Content\Handler(
            $this->createMock(ContentGateway::class),
            new Content\Location\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                new CriteriaConverter(
                    array(
                        new LocationCriterionHandler\LocationId($this->getDatabaseHandler()),
                        new LocationCriterionHandler\ParentLocationId($this->getDatabaseHandler()),
                        new CommonCriterionHandler\LogicalAnd($this->getDatabaseHandler()),
                        new CommonCriterionHandler\MatchAll($this->getDatabaseHandler()),
                        new CommonCriterionHandler\SectionId($this->getDatabaseHandler()),
                        new CommonCriterionHandler\ContentTypeIdentifier(
                            $this->getDatabaseHandler(),
                            $this->getContentTypeHandler()
                        ),
                    )
                ),
                new SortClauseConverter(
                    array(
                        new LocationSortClauseHandler\Location\Id($this->getDatabaseHandler()),
                        new LocationSortClauseHandler\Location\Depth($this->getDatabaseHandler()),
                        new LocationSortClauseHandler\Location\Path($this->getDatabaseHandler()),
                        new LocationSortClauseHandler\Location\Priority($this->getDatabaseHandler()),
                        new LocationSortClauseHandler\Location\Visibility($this->getDatabaseHandler()),
                        new LocationSortClauseHandler\Location\IsMainLocation($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\ContentId($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\ContentName($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\DateModified($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\DatePublished($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\SectionIdentifier($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\SectionName($this->getDatabaseHandler()),
                        new CommonSortClauseHandler\Field(
                            $this->getDatabaseHandler(),
                            $this->getLanguageHandler(),
                            $this->getContentTypeHandler()
                        ),
                    )
                ),
                $this->getLanguageHandler()
            ),
            new Content\WordIndexer\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                $this->getContentTypeHandler(),
                $this->getDefinitionBasedTransformationProcessor(),
                new Content\WordIndexer\Repository\SearchIndex($this->getDatabaseHandler()),
                $this->getFullTextSearchConfiguration()
            ),
            $this->createMock(ContentMapper::class),
            $this->getLocationMapperMock(),
            $this->getLanguageHandler(),
            $this->getFullTextMapper($this->getContentTypeHandler())
        );
    }

    /**
     * Returns a location mapper mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapperMock()
    {
        $mapperMock = $this->getMockBuilder(LocationMapper::class)
            ->setMethods(array('createLocationsFromRows'))
            ->getMock();
        $mapperMock
            ->expects($this->any())
            ->method('createLocationsFromRows')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function ($rows) {
                        $locations = array();
                        foreach ($rows as $row) {
                            $locationId = (int)$row['node_id'];
                            if (!isset($locations[$locationId])) {
                                $locations[$locationId] = new SPILocation();
                                $locations[$locationId]->id = $locationId;
                            }
                        }

                        return array_values($locations);
                    }
                )
            );

        return $mapperMock;
    }

    public function testNoSorting()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\ParentLocationId(array(178)),
                    'offset' => 0,
                    'limit' => 5,
                    'sortClauses' => array(),
                )
            )
        );

        $ids = $this->getIds($locations);
        sort($ids);
        $this->assertEquals(
            array(179, 180, 181, 182, 183),
            $ids
        );
    }

    public function testSortLocationPath()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\ParentLocationId(array(178)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(new SortClause\Location\Path(LocationQuery::SORT_DESC)),
                )
            )
        );

        $this->assertSearchResults(
            array(186, 185, 184, 183, 182, 181, 180, 179),
            $locations
        );
    }

    public function testSortLocationDepth()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(148, 167, 169, 172)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(new SortClause\Location\Depth(LocationQuery::SORT_ASC)),
                )
            )
        );

        $this->assertSearchResults(
            array(167, 172, 169, 148),
            $locations
        );
    }

    public function testSortLocationDepthAndPath()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(141, 142, 143, 144, 146, 147)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\Location\Depth(LocationQuery::SORT_ASC),
                        new SortClause\Location\Path(LocationQuery::SORT_DESC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(147, 146, 141, 144, 143, 142),
            $locations
        );
    }

    public function testSortLocationPriority()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(149, 156, 167)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\Location\Priority(LocationQuery::SORT_DESC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(167, 156, 149),
            $locations
        );
    }

    public function testSortDateModified()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(148, 167, 169, 172)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\DateModified(),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(169, 172, 167, 148),
            $locations
        );
    }

    public function testSortDatePublished()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(148, 167, 169, 172)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\DatePublished(LocationQuery::SORT_DESC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(148, 172, 169, 167),
            $locations
        );
    }

    public function testSortSectionIdentifier()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(
                        array(5, 43, 45, 48, 51, 54, 156, 157)
                    ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\SectionIdentifier(),
                    ),
                )
            )
        );

        // First, results of section 2 should appear, then the ones of 3, 4 and 6
        // From inside a specific section, no particular order should be defined
        // the logic is then to have a set of sorted id's to compare with
        // the comparison being done slice by slice.
        $idMapSet = array(
            2 => array(5, 45),
            3 => array(43, 51),
            4 => array(48, 54),
            6 => array(156, 157),
        );
        $locationIds = $this->getIds($locations);
        $index = 0;

        foreach ($idMapSet as $idSet) {
            $locationIdsSubset = array_slice($locationIds, $index, $count = count($idSet));
            $index += $count;
            sort($locationIdsSubset);
            $this->assertEquals(
                $idSet,
                $locationIdsSubset
            );
        }
    }

    public function testSortContentName()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(13, 15, 44, 45, 228)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\ContentName(),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(228, 15, 13, 45, 44),
            $locations
        );
    }

    public function testSortContentId()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(13, 15, 44, 45, 228)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\ContentId(),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(45, 13, 15, 44, 228),
            $locations
        );
    }

    public function testSortLocationId()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(13, 15, 44, 45, 228)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Location\Id(LocationQuery::SORT_DESC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(228, 45, 44, 15, 13),
            $locations
        );
    }

    public function testSortLocationVisibilityAscending()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(45, 228)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Location\Visibility(LocationQuery::SORT_ASC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(45, 228),
            $locations
        );
    }

    public function testSortLocationVisibilityDescending()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LocationId(array(45, 228)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Location\Visibility(LocationQuery::SORT_DESC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(228, 45),
            $locations
        );
    }

    public function testSortSectionName()
    {
        $handler = $this->getContentSearchHandler();

        $result = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\SectionId(array(4, 2, 6, 3)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\SectionName(),
                    ),
                )
            )
        );

        // First, results of section "Media" should appear, then the ones of "Protected",
        // "Setup" and "Users"
        // From inside a specific section, no particular order should be defined
        // the logic is then to have a set of sorted id's to compare with
        // the comparison being done slice by slice.
        $idMapSet = array(
            'media' => array(43, 51, 52, 53, 59, 60, 61, 62, 63, 64, 65, 66, 68, 202, 203),
            'protected' => array(156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 166),
            'setup' => array(48, 54),
            'users' => array(5, 12, 13, 14, 15, 44, 45, 228),
        );
        $locationIds = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $result->searchHits
        );

        $expectedCount = 0;
        foreach ($idMapSet as $set) {
            $expectedCount += count($set);
        }

        $this->assertEquals($expectedCount, $result->totalCount);

        $index = 0;
        foreach ($idMapSet as $idSet) {
            $locationIdsSubset = array_slice($locationIds, $index, $count = count($idSet));
            $index += $count;
            sort($locationIdsSubset);
            $this->assertEquals(
                $idSet,
                $locationIdsSubset
            );
        }
    }

    public function testSortFieldText()
    {
        $handler = $this->getContentSearchHandler();

        $result = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\SectionId(array(1)),
                            new Criterion\ContentTypeIdentifier(array('article')),
                        )
                    ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Field('article', 'title', LocationQuery::SORT_ASC, 'eng-US'),
                    ),
                )
            )
        );

        // There are several identical titles, need to take care about this
        $idMapSet = array(
            'aenean malesuada ligula' => array(85),
            'aliquam pulvinar suscipit tellus' => array(104),
            'asynchronous publishing' => array(150, 217),
            'canonical links' => array(149, 218),
            'class aptent taciti' => array(90),
            'class aptent taciti sociosqu' => array(84),
            'duis auctor vehicula erat' => array(91),
            'etiam posuere sodales arcu' => array(80),
            'etiam sodales mauris' => array(89),
            'ez publish enterprise' => array(153),
            'fastcgi' => array(146, 220),
            'fusce sagittis sagittis' => array(79),
            'fusce sagittis sagittis urna' => array(83),
            'get involved' => array(109),
            'how to develop with ez publish' => array(129, 213),
            'how to manage ez publish' => array(120, 204),
            'how to use ez publish' => array(110, 195),
            'improved block editing' => array(138),
            'improved front-end editing' => array(141),
            'improved user registration workflow' => array(134),
            'in hac habitasse platea' => array(81),
            'lots of websites, one ez publish installation' => array(132),
            'rest api interface' => array(152, 216),
            'separate content & design in ez publish' => array(193),
            'support for red hat enterprise' => array(147, 219),
            'tutorials for' => array(108),
        );
        $locationIds = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $result->searchHits
        );
        $index = 0;

        foreach ($idMapSet as $idSet) {
            $locationIdsSubset = array_slice($locationIds, $index, $count = count($idSet));
            $index += $count;
            sort($locationIdsSubset);
            $this->assertEquals(
                $idSet,
                $locationIdsSubset
            );
        }
    }

    public function testSortFieldNumeric()
    {
        $handler = $this->getContentSearchHandler();

        $result = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\SectionId(array(1)),
                            new Criterion\ContentTypeIdentifier('product'),
                        )
                    ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Field('product', 'price', LocationQuery::SORT_ASC, 'eng-US'),
                    ),
                )
            )
        );

        $this->assertEquals(
            array(75, 73, 74, 71),
            array_map(
                function ($hit) {
                    return $hit->valueObject->id;
                },
                $result->searchHits
            )
        );
    }

    public function testSortIsMainLocationAscending()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\ParentLocationId(224),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Location\IsMainLocation(LocationQuery::SORT_ASC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(510, 225),
            $locations
        );
    }

    public function testSortIsMainLocationDescending()
    {
        $handler = $this->getContentSearchHandler();

        $locations = $handler->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\ParentLocationId(224),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Location\IsMainLocation(LocationQuery::SORT_DESC),
                    ),
                )
            )
        );

        $this->assertSearchResults(
            array(225, 510),
            $locations
        );
    }
}
