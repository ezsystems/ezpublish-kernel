<?php

/**
 * File containing the SearchServiceLocationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Test case for Location operations in the SearchService.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class SearchServiceLocationTest extends BaseTest
{
    const QUERY_CLASS = LocationQuery::class;

    use Common\FacetedSearchProvider;

    protected function setUp()
    {
        $setupFactory = $this->getSetupFactory();
        if ($setupFactory instanceof LegacyElasticsearch) {
            $this->markTestSkipped('Field Location search is not yet implemented Elasticsearch search engine');
        }

        parent::setUp();
    }

    /**
     * Test for the findLocation() method.
     *
     * @dataProvider getFacetedSearches
     * @see \eZ\Publish\API\Repository\SearchService::findLoctions()
     */
    public function testFindFacetedLocation(LocationQuery $query, $fixture)
    {
        $this->assertQueryFixture($query, $fixture);
    }

    /**
     * Create test Content with ezcountry field having multiple countries selected.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createMultipleCountriesContent()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct('countries-multiple');
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->remoteId = 'countries-multiple-123';
        $createStruct->names = ['eng-GB' => 'Multiple countries'];
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new \DateTime();

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('countries', 'ezcountry');
        $fieldCreate->names = ['eng-GB' => 'Countries'];
        $fieldCreate->fieldGroup = 'main';
        $fieldCreate->position = 1;
        $fieldCreate->isTranslatable = false;
        $fieldCreate->isSearchable = true;
        $fieldCreate->fieldSettings = ['isMultiple' => true];

        $createStruct->addFieldDefinition($fieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($createStruct, [$contentGroup]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->remoteId = 'countries-multiple-456';
        $createStruct->alwaysAvailable = false;
        $createStruct->setField(
            'countries',
            ['BE', 'DE', 'FR', 'HR', 'NO', 'PT', 'RU']
        );

        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);
        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $content = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        return $content;
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testFieldCollectionContains()
    {
        $testContent = $this->createMultipleCountriesContent();

        $query = new LocationQuery(
            [
                'query' => new Criterion\Field(
                    'countries',
                    Criterion\Operator::CONTAINS,
                    'Belgium'
                ),
            ]
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $testContent->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testFieldCollectionContains
     */
    public function testFieldCollectionContainsNoMatch()
    {
        $this->createMultipleCountriesContent();
        $query = new LocationQuery(
            [
                'query' => new Criterion\Field(
                    'countries',
                    Criterion\Operator::CONTAINS,
                    'Netherlands Antilles'
                ),
            ]
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(0, $result->totalCount);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testInvalidFieldIdentifierRange()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findLocations(
            new LocationQuery(
                [
                    'filter' => new Criterion\Field(
                        'some_hopefully_unknown_field',
                        Criterion\Operator::BETWEEN,
                        [10, 1000]
                    ),
                    'sortClauses' => [new SortClause\ContentId()],
                ]
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testInvalidFieldIdentifierIn()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findLocations(
            new LocationQuery(
                [
                    'filter' => new Criterion\Field(
                        'some_hopefully_unknown_field',
                        Criterion\Operator::EQ,
                        1000
                    ),
                    'sortClauses' => [new SortClause\ContentId()],
                ]
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocationsWithNonSearchableField()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findLocations(
            new LocationQuery(
                [
                    'filter' => new Criterion\Field(
                        'tag_cloud_url',
                        Criterion\Operator::EQ,
                        'http://nimbus.com'
                    ),
                    'sortClauses' => [new SortClause\ContentId()],
                ]
            )
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $result
     *
     * @return array
     */
    protected function mapResultLocationIds(SearchResult $result)
    {
        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject->id;
            },
            $result->searchHits
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testQueryCustomField()
    {
        $query = new LocationQuery(
            [
                'query' => new Criterion\CustomField(
                    'custom_field',
                    Criterion\Operator::EQ,
                    'AdMiNiStRaToR'
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [new SortClause\ContentId()],
            ]
        );
        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/QueryCustomField.php',
            null,
            true
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * This tests explicitly queries the first_name while user is contained in
     * the last_name of admin and anonymous. This is done to show the custom
     * copy field working.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testQueryModifiedField()
    {
        // Check using get_class since the others extend SetupFactory\Legacy
        if (ltrim(get_class($this->getSetupFactory()), '\\') === 'eZ\Publish\API\Repository\Tests\SetupFactory\Legacy') {
            $this->markTestIncomplete(
                'Custom fields not supported by LegacySE ' .
                '(@todo: Legacy should fallback to just querying normal field so this should be tested here)'
            );
        }

        $query = new LocationQuery(
            [
                'query' => new Criterion\Field(
                    'first_name',
                    Criterion\Operator::EQ,
                    'User'
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [new SortClause\ContentId()],
            ]
        );
        $query->query->setCustomField('user', 'first_name', 'custom_field');

        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/QueryModifiedField.php',
            null,
            true
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestPlaceContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct('testtype');
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->names = ['eng-GB' => 'Test type'];
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new \DateTime();

        $translatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('maplocation', 'ezgmaplocation');
        $translatableFieldCreate->names = ['eng-GB' => 'Map location field'];
        $translatableFieldCreate->fieldGroup = 'main';
        $translatableFieldCreate->position = 1;
        $translatableFieldCreate->isTranslatable = false;
        $translatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition($translatableFieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($createStruct, [$contentGroup]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        return $contentType;
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @group maplocation
     */
    public function testMapLocationDistanceLessThanOrEqual()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            [
                'filter' => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::LTE,
                            240,
                            43.756825,
                            15.775074
                        ),
                    ]
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [],
            ]
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $wildBoars->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @group maplocation
     */
    public function testMapLocationDistanceGreaterThanOrEqual()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            [
                'filter' => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            240,
                            43.756825,
                            15.775074
                        ),
                    ]
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [],
            ]
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $tree->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @group maplocation
     */
    public function testMapLocationDistanceBetween()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $query = new LocationQuery(
            [
                'filter' => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::BETWEEN,
                            [239, 241],
                            43.756825,
                            15.775074
                        ),
                    ]
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [],
            ]
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $mushrooms->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @group maplocation
     */
    public function testMapLocationDistanceSortAscending()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $wellInVodice = [
            'latitude' => 43.756825,
            'longitude' => 15.775074,
        ];

        $query = new LocationQuery(
            [
                'filter' => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            235,
                            $wellInVodice['latitude'],
                            $wellInVodice['longitude']
                        ),
                    ]
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [
                    new SortClause\MapLocationDistance(
                        'testtype',
                        'maplocation',
                        $wellInVodice['latitude'],
                        $wellInVodice['longitude'],
                        LocationQuery::SORT_ASC
                    ),
                ],
            ]
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(3, $result->totalCount);
        $this->assertEquals(
            $wildBoars->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
        $this->assertEquals(
            $mushrooms->contentInfo->mainLocationId,
            $result->searchHits[1]->valueObject->id
        );
        $this->assertEquals(
            $tree->contentInfo->mainLocationId,
            $result->searchHits[2]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @group maplocation
     */
    public function testMapLocationDistanceSortDescending()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $well = [
            'latitude' => 43.756825,
            'longitude' => 15.775074,
        ];

        $query = new LocationQuery(
            [
                'filter' => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            235,
                            $well['latitude'],
                            $well['longitude']
                        ),
                    ]
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [
                    new SortClause\MapLocationDistance(
                        'testtype',
                        'maplocation',
                        $well['latitude'],
                        $well['longitude'],
                        LocationQuery::SORT_DESC
                    ),
                ],
            ]
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(3, $result->totalCount);
        $this->assertEquals(
            $wildBoars->contentInfo->mainLocationId,
            $result->searchHits[2]->valueObject->id
        );
        $this->assertEquals(
            $mushrooms->contentInfo->mainLocationId,
            $result->searchHits[1]->valueObject->id
        );
        $this->assertEquals(
            $tree->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @group maplocation
     */
    public function testMapLocationDistanceWithCustomField()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $distanceCriterion = new Criterion\MapLocationDistance(
            'maplocation',
            Criterion\Operator::LTE,
            240,
            43.756825,
            15.775074
        );
        $distanceCriterion->setCustomField('testtype', 'maplocation', 'custom_geolocation_field');

        $query = new LocationQuery(
            [
                'filter' => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeId($contentType->id),
                        $distanceCriterion,
                    ]
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [],
            ]
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $wildBoars->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @group maplocation
     */
    public function testMapLocationDistanceWithCustomFieldSort()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft($contentType);
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct(2);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.894877,
                'longitude' => 15.972699,
                'address' => 'Here be wild boars',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $wildBoars = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.927334,
                'longitude' => 15.934847,
                'address' => 'A lone tree',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $tree = $contentService->publishVersion($draft->getVersionInfo());

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->setField(
            'maplocation',
            [
                'latitude' => 45.903777,
                'longitude' => 15.958788,
                'address' => 'Meadow with mushrooms',
            ],
            'eng-GB'
        );

        $draft = $contentService->createContent($createStruct, [$locationCreateStruct]);
        $mushrooms = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        $well = [
            'latitude' => 43.756825,
            'longitude' => 15.775074,
        ];

        $sortClause = new SortClause\MapLocationDistance(
            'testtype',
            'maplocation',
            $well['latitude'],
            $well['longitude'],
            LocationQuery::SORT_DESC
        );
        $sortClause->setCustomField('testtype', 'maplocation', 'custom_geolocation_field');

        $query = new LocationQuery(
            [
                'filter' => new Criterion\LogicalAnd(
                    [
                        new Criterion\ContentTypeId($contentType->id),
                        new Criterion\MapLocationDistance(
                            'maplocation',
                            Criterion\Operator::GTE,
                            235,
                            $well['latitude'],
                            $well['longitude']
                        ),
                    ]
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => [
                    $sortClause,
                ],
            ]
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations($query);

        $this->assertEquals(3, $result->totalCount);
        $this->assertEquals(
            $wildBoars->contentInfo->mainLocationId,
            $result->searchHits[2]->valueObject->id
        );
        $this->assertEquals(
            $mushrooms->contentInfo->mainLocationId,
            $result->searchHits[1]->valueObject->id
        );
        $this->assertEquals(
            $tree->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     */
    public function testVisibilityCriterionWithHiddenContent()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $searchService = $repository->getSearchService();

        $testRootContentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $testRootContentCreate->setField('name', 'Root for test');

        $rootContent = $contentService->createContent(
            $testRootContentCreate,
            [
                $locationService->newLocationCreateStruct(
                    $this->generateId('location', 2)
                ),
            ]
        );

        $publishedRootContent = $contentService->publishVersion($rootContent->versionInfo);

        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreate->setField('name', 'To Hide');

        $content = $contentService->createContent(
            $contentCreate,
            [
                $locationService->newLocationCreateStruct(
                    $publishedRootContent->contentInfo->mainLocationId
                ),
            ]
        );
        $publishedContent = $contentService->publishVersion($content->versionInfo);

        $childContentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $childContentCreate->setField('name', 'Invisible Child');

        $childContent = $contentService->createContent(
            $childContentCreate,
            [
                $locationService->newLocationCreateStruct(
                    $publishedContent->contentInfo->mainLocationId
                ),
            ]
        );
        $rootLocation = $locationService->loadLocation($publishedRootContent->contentInfo->mainLocationId);

        $contentService->publishVersion($childContent->versionInfo);
        $this->refreshSearch($repository);

        $query = new LocationQuery([
            'query' => new Criterion\LogicalAnd([
                new Criterion\Visibility(
                    Criterion\Visibility::VISIBLE
                ),
                new Criterion\Subtree(
                    $rootLocation->pathString
                ),
            ]),
        ]);

        //Sanity check for visible locations
        $result = $searchService->findLocations($query);
        $this->assertEquals(3, $result->totalCount);

        //Hide main content
        $contentService->hideContent($publishedContent->contentInfo);
        $this->refreshSearch($repository);

        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);

        //Query for invisible content
        $hiddenQuery = new LocationQuery([
            'query' => new Criterion\LogicalAnd([
                new Criterion\Visibility(
                    Criterion\Visibility::HIDDEN
                ),
                new Criterion\Subtree(
                    $rootLocation->pathString
                ),
            ]),
        ]);

        $result = $searchService->findLocations($hiddenQuery);
        $this->assertEquals(2, $result->totalCount);
    }

    /**
     * Assert that query result matches the given fixture.
     *
     * @param LocationQuery $query
     * @param string $fixture
     * @param null|callable $closure
     */
    protected function assertQueryFixture(LocationQuery $query, $fixture, $closure = null, $ignoreScore = true)
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        try {
            $result = $searchService->findLocations($query);
            $this->simplifySearchResult($result);
        } catch (NotImplementedException $e) {
            $this->markTestSkipped(
                'This feature is not supported by the current search backend: ' . $e->getMessage()
            );
        }

        if (!is_file($fixture)) {
            if (isset($_ENV['ez_tests_record'])) {
                file_put_contents(
                    $record = $fixture . '.recording',
                    "<?php\n\nreturn " . var_export($result, true) . ";\n\n"
                );
                $this->markTestIncomplete("No fixture available. Result recorded at $record. Result: \n" . $this->printResult($result));
            } else {
                $this->markTestIncomplete("No fixture available. Set \$_ENV['ez_tests_record'] to generate:\n " . $fixture);
            }
        }

        $fixture = include $fixture;

        if ($closure !== null) {
            $closure($result);
        }

        if ($ignoreScore) {
            foreach ([$fixture, $result] as $result) {
                $property = new \ReflectionProperty(get_class($result), 'maxScore');
                $property->setAccessible(true);
                $property->setValue($result, 0.0);

                foreach ($result->searchHits as $hit) {
                    $property = new \ReflectionProperty(get_class($hit), 'score');
                    $property->setAccessible(true);
                    $property->setValue($hit, 0.0);
                }
            }
        }

        foreach ([$fixture, $result] as $set) {
            foreach ($set->searchHits as $hit) {
                $property = new \ReflectionProperty(get_class($hit), 'index');
                $property->setAccessible(true);
                $property->setValue($hit, null);

                $property = new \ReflectionProperty(get_class($hit), 'matchedTranslation');
                $property->setAccessible(true);
                $property->setValue($hit, null);
            }
        }

        $this->assertEquals(
            $fixture,
            $result,
            'Search results do not match.',
            .2 // Be quite generous regarding delay -- most important for scores
        );
    }

    /**
     * Show a simplified view of the search result for manual introspection.
     *
     * @param SearchResult $result
     *
     * @return string
     */
    protected function printResult(SearchResult $result)
    {
        $printed = '';
        foreach ($result->searchHits as $hit) {
            $printed .= sprintf(" - %s (%s)\n", $hit->valueObject['title'], $hit->valueObject['id']);
        }

        return $printed;
    }

    /**
     * Simplify search result.
     *
     * This leads to saner comparisons of results, since we do not get the full
     * content objects every time.
     *
     * @param SearchResult $result
     */
    protected function simplifySearchResult(SearchResult $result)
    {
        $result->time = 1;

        foreach ($result->searchHits as $hit) {
            switch (true) {
                case $hit->valueObject instanceof Location:
                    $hit->valueObject = [
                        'id' => $hit->valueObject->contentInfo->id,
                        'title' => $hit->valueObject->contentInfo->name,
                    ];
                    break;

                default:
                    throw new \RuntimeException('Unknown search result hit type: ' . get_class($hit->valueObject));
            }
        }
    }

    /**
     * Get fixture directory.
     *
     * @return string
     */
    protected function getFixtureDir()
    {
        return __DIR__ . '/_fixtures/' . getenv('fixtureDir') . '/';
    }
}
