<?php
/**
 * File containing the SearchServiceLocationTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacySolr;

/**
 * Test case for Location operations in the SearchService.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class SearchServiceLocationTest extends BaseTest
{
    protected function setUp()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Location search handler is not yet implemented for Solr storage" );
        }

        if ( $setupFactory instanceof LegacyElasticsearch )
        {
            $this->markTestSkipped( "Field search is not yet implemented for Elasticsearch storage" );
        }

        parent::setUp();
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

        $createStruct = $contentTypeService->newContentTypeCreateStruct( "countries-multiple" );
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->remoteId = "countries-multiple-123";
        $createStruct->names = array( "eng-GB" => "Multiple countries" );
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new \DateTime();

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "countries", "ezcountry" );
        $fieldCreate->names = array( "eng-GB" => "Countries" );
        $fieldCreate->fieldGroup = "main";
        $fieldCreate->position = 1;
        $fieldCreate->isTranslatable = false;
        $fieldCreate->isSearchable = true;
        $fieldCreate->fieldSettings = array( "isMultiple" => true );

        $createStruct->addFieldDefinition( $fieldCreate );

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier( "Content" );
        $contentTypeDraft = $contentTypeService->createContentType( $createStruct, array( $contentGroup ) );
        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        $contentType = $contentTypeService->loadContentType( $contentTypeDraft->id );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->remoteId = "countries-multiple-456";
        $createStruct->alwaysAvailable = false;
        $createStruct->setField(
            "countries",
            array( "BE", "DE", "FR", "HR", "NO", "PT", "RU" )
        );

        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );
        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $content = $contentService->publishVersion( $draft->getVersionInfo() );

        return $content;
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldCollectionContains()
    {
        $testContent = $this->createMultipleCountriesContent();

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\Field(
                    "countries",
                    Criterion\Operator::CONTAINS,
                    "Belgium"
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 1, $result->totalCount );
        $this->assertEquals(
            $testContent->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testFieldCollectionContains
     */
    public function testFieldCollectionContainsNoMatch()
    {
        $this->createMultipleCountriesContent();
        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\Field(
                    "countries",
                    Criterion\Operator::CONTAINS,
                    "Netherlands Antilles"
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 0, $result->totalCount );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testInvalidFieldIdentifierRange()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\Field(
                        'some_hopefully_unknown_field',
                        Criterion\Operator::BETWEEN,
                        array( 10, 1000 )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testInvalidFieldIdentifierIn()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\Field(
                        'some_hopefully_unknown_field',
                        Criterion\Operator::EQ,
                        1000
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindLocationsWithNonSearchableField()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findLocations(
            new LocationQuery(
                array(
                    'filter' => new Criterion\Field(
                        'tag_cloud_url',
                        Criterion\Operator::EQ,
                        'http://nimbus.com'
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                )
            )
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct( "test-type" );
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->names = array( "eng-GB" => "Test type" );
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new \DateTime();

        $translatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "integer", "ezinteger" );
        $translatableFieldCreate->names = array( "eng-GB" => "Simple translatable integer field" );
        $translatableFieldCreate->fieldGroup = "main";
        $translatableFieldCreate->position = 1;
        $translatableFieldCreate->isTranslatable = true;
        $translatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition( $translatableFieldCreate );

        $nonTranslatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "integer2", "ezinteger" );
        $nonTranslatableFieldCreate->names = array( "eng-GB" => "Simple non-translatable integer field" );
        $nonTranslatableFieldCreate->fieldGroup = "main";
        $nonTranslatableFieldCreate->position = 2;
        $nonTranslatableFieldCreate->isTranslatable = false;
        $nonTranslatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition( $nonTranslatableFieldCreate );

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier( "Content" );
        $contentTypeDraft = $contentTypeService->createContentType( $createStruct, array( $contentGroup ) );
        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        $contentType = $contentTypeService->loadContentType( $contentTypeDraft->id );

        return $contentType;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param int $fieldValue11 Value for translatable field in first language
     * @param int $fieldValue12 Value for translatable field in second language
     * @param int $fieldValue2 Value for non translatable field
     * @param string $mainLanguageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createMultilingualContent(
        $contentType,
        $fieldValue11,
        $fieldValue12,
        $fieldValue2 = null,
        $mainLanguageCode = "eng-GB"
    )
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = $mainLanguageCode;
        $createStruct->setField( "integer", $fieldValue11, "eng-GB" );
        $createStruct->setField( "integer", $fieldValue12, "ger-DE" );
        $createStruct->setField( "integer2", $fieldValue2, $mainLanguageCode );

        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );
        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $content = $contentService->publishVersion( $draft->getVersionInfo() );

        return $content;
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSort()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_DESC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_ASC, "ger-DE" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );

        /**
         * Expected order, Value eng-GB, Value ger-DE
         *
         * Content 3, 2, 3
         * Content 2, 2, 4
         * Content 4, 1, 1
         * Content 1, 1, 2
         */

        $this->assertEquals(
            array(
                $contentIdList[3],
                $contentIdList[2],
                $contentIdList[4],
                $contentIdList[1],
            ),
            $this->mapResultLocationIds( $result )
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortVariant2()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_ASC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_DESC, "ger-DE" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );

        /**
         * Expected order, Value eng-GB, Value ger-DE
         *
         * Content 1, 1, 2
         * Content 4, 1, 1
         * Content 2, 2, 4
         * Content 3, 2, 3
         */

        $this->assertEquals(
            array(
                $contentIdList[1],
                $contentIdList[4],
                $contentIdList[2],
                $contentIdList[3],
            ),
            $this->mapResultLocationIds( $result )
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortVariant3()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_DESC, "ger-DE" ),
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_ASC, "eng-GB" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );

        /**
         * Expected order, Value eng-GB, Value ger-DE
         *
         * Content 2, 2, 4
         * Content 3, 2, 3
         * Content 1, 1, 2
         * Content 4, 1, 1
         */

        $this->assertEquals(
            array(
                $contentIdList[2],
                $contentIdList[3],
                $contentIdList[1],
                $contentIdList[4],
            ),
            $this->mapResultLocationIds( $result )
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSearchWithFieldSortThrowsInvalidArgumentExceptionTranslatableField()
    {
        $contentType = $this->createTestContentType();
        $this->createMultilingualContent( $contentType, 1, 2 );

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_ASC ),
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $searchService->findLocations( $query );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSearchWithFieldSortThrowsInvalidArgumentExceptionNonTranslatableField()
    {
        $contentType = $this->createTestContentType();
        $this->createMultilingualContent( $contentType, 1, 2, 3, "eng-GB" );

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    // The main language can change, so no language code allowed on non-translatable field whatsoever
                    new SortClause\Field( "test-type", "integer2", LocationQuery::SORT_ASC, "eng-GB" ),
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $searchService->findLocations( $query );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortWithNonTranslatableField()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_DESC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer2", LocationQuery::SORT_ASC ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );

        /**
         * Expected order, Value eng-GB, Value non-translatable
         *
         * Content 2, 2, 3
         * Content 3, 2, 4
         * Content 1, 1, 1
         * Content 4, 1, 2
         */

        $this->assertEquals(
            array(
                $contentIdList[2],
                $contentIdList[3],
                $contentIdList[1],
                $contentIdList[4],
            ),
            $this->mapResultLocationIds( $result )
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortWithDefaultLanguage()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_ASC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer2", LocationQuery::SORT_DESC ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );

        /**
         * Expected order, Value eng-GB, Value non-translatable
         *
         * Content 4, 1, 2
         * Content 1, 1, 1
         * Content 3, 2, 4
         * Content 2, 2, 3
         */

        $this->assertEquals(
            array(
                $contentIdList[4],
                $contentIdList[1],
                $contentIdList[3],
                $contentIdList[2],
            ),
            $this->mapResultLocationIds( $result )
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortWithDefaultLanguageVariant2()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "eng-GB" )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "eng-GB" )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer2", LocationQuery::SORT_DESC ),
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_DESC, "ger-DE" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );

        /**
         * Expected order, Value non-translatable, Value ger-DE
         *
         * Content 3, 4, 3
         * Content 2, 3, 4
         * Content 4, 2, 1
         * Content 1, 1, 2
         */

        $this->assertEquals(
            array(
                $contentIdList[3],
                $contentIdList[2],
                $contentIdList[4],
                $contentIdList[1],
            ),
            $this->mapResultLocationIds( $result )
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortUnusedLanguageDoesNotFilterResultSet()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    // "test-type" Content instance do not exist in "eng-US"
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_ASC, "eng-US" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testMultilingualFieldSortUnusedLanguageDoesNotFilterResultSet
     */
    public function testMultilingualFieldSortUnusedLanguageDoesNotChangeSort()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "eng-GB" )->contentInfo->mainLocationId;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "eng-GB" )->contentInfo->mainLocationId;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->contentInfo->mainLocationId;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->contentInfo->mainLocationId;

        $query = new LocationQuery(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    // "test-type" Content instance do not exist in "eng-US"
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_DESC, "eng-US" ),
                    new SortClause\Field( "test-type", "integer", LocationQuery::SORT_ASC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer2", LocationQuery::SORT_ASC ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 4, $result->totalCount );

        /**
         * Expected order, Value eng-GB, Value non-translatable
         *
         * Content 1, 1, 1
         * Content 4, 1, 2
         * Content 2, 2, 3
         * Content 3, 2, 4
         */

        $this->assertEquals(
            array(
                $contentIdList[1],
                $contentIdList[4],
                $contentIdList[2],
                $contentIdList[3],
            ),
            $this->mapResultLocationIds( $result )
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $result
     *
     * @return array
     */
    protected function mapResultLocationIds( SearchResult $result )
    {
        return array_map(
            function ( SearchHit $searchHit )
            {
                return $searchHit->valueObject->id;
            },
            $result->searchHits
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testQueryCustomField()
    {
        $query = new LocationQuery(
            array(
                'query' => new Criterion\CustomField(
                    'custom_field',
                    Criterion\Operator::EQ,
                    'AdMiNiStRaToR'
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array( new SortClause\ContentId() )
            )
        );
        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/QueryCustomField.php'
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testQueryModifiedField()
    {
        $query = new LocationQuery(
            array(
                'query' => new Criterion\Field(
                    'first_name',
                    Criterion\Operator::EQ,
                    'User'
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array( new SortClause\ContentId() )
            )
        );
        $query->query->setCustomField( 'user', 'first_name', 'custom_field' );

        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/QueryModifiedField.php'
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestPlaceContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct( "testtype" );
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->names = array( "eng-GB" => "Test type" );
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new \DateTime();

        $translatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "maplocation", "ezgmaplocation" );
        $translatableFieldCreate->names = array( "eng-GB" => "Map location field" );
        $translatableFieldCreate->fieldGroup = "main";
        $translatableFieldCreate->position = 1;
        $translatableFieldCreate->isTranslatable = false;
        $translatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition( $translatableFieldCreate );

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier( "Content" );
        $contentTypeDraft = $contentTypeService->createContentType( $createStruct, array( $contentGroup ) );
        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        $contentType = $contentTypeService->loadContentType( $contentTypeDraft->id );

        return $contentType;
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group maplocation
     */
    public function testMapLocationDistanceLessThanOrEqual()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft( $contentType );
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.894877,
                "longitude" => 15.972699,
                "address" => "Here be wild boars",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $wildBoars = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.927334,
                "longitude" => 15.934847,
                "address" => "A lone tree",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $tree = $contentService->publishVersion( $draft->getVersionInfo() );

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId( $contentType->id ),
                        new Criterion\MapLocationDistance(
                            "maplocation",
                            Criterion\Operator::LTE,
                            240,
                            43.756825,
                            15.775074
                        )
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array()
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 1, $result->totalCount );
        $this->assertEquals(
            $wildBoars->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group maplocation
     */
    public function testMapLocationDistanceGreaterThanOrEqual()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft( $contentType );
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.894877,
                "longitude" => 15.972699,
                "address" => "Here be wild boars",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $wildBoars = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.927334,
                "longitude" => 15.934847,
                "address" => "A lone tree",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $tree = $contentService->publishVersion( $draft->getVersionInfo() );

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId( $contentType->id ),
                        new Criterion\MapLocationDistance(
                            "maplocation",
                            Criterion\Operator::GTE,
                            240,
                            43.756825,
                            15.775074
                        )
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array()
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 1, $result->totalCount );
        $this->assertEquals(
            $tree->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group maplocation
     */
    public function testMapLocationDistanceBetween()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft( $contentType );
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.894877,
                "longitude" => 15.972699,
                "address" => "Here be wild boars",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $wildBoars = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.927334,
                "longitude" => 15.934847,
                "address" => "A lone tree",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $tree = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.903777,
                "longitude" => 15.958788,
                "address" => "Meadow with mushrooms",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $mushrooms = $contentService->publishVersion( $draft->getVersionInfo() );

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId( $contentType->id ),
                        new Criterion\MapLocationDistance(
                            "maplocation",
                            Criterion\Operator::BETWEEN,
                            array( 239, 241 ),
                            43.756825,
                            15.775074
                        )
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array()
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 1, $result->totalCount );
        $this->assertEquals(
            $mushrooms->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group maplocation
     */
    public function testMapLocationDistanceSortAscending()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft( $contentType );
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.894877,
                "longitude" => 15.972699,
                "address" => "Here be wild boars",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $wildBoars = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.927334,
                "longitude" => 15.934847,
                "address" => "A lone tree",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $tree = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.903777,
                "longitude" => 15.958788,
                "address" => "Meadow with mushrooms",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $mushrooms = $contentService->publishVersion( $draft->getVersionInfo() );

        $wellInVodice = array(
            "latitude" => 43.756825,
            "longitude" => 15.775074
        );

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId( $contentType->id ),
                        new Criterion\MapLocationDistance(
                            "maplocation",
                            Criterion\Operator::GTE,
                            235,
                            $wellInVodice["latitude"],
                            $wellInVodice["longitude"]
                        )
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        "testtype",
                        "maplocation",
                        $wellInVodice["latitude"],
                        $wellInVodice["longitude"],
                        LocationQuery::SORT_ASC
                    )
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 3, $result->totalCount );
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group maplocation
     */
    public function testMapLocationDistanceSortDescending()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft( $contentType );
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.894877,
                "longitude" => 15.972699,
                "address" => "Here be wild boars",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $wildBoars = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.927334,
                "longitude" => 15.934847,
                "address" => "A lone tree",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $tree = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.903777,
                "longitude" => 15.958788,
                "address" => "Meadow with mushrooms",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $mushrooms = $contentService->publishVersion( $draft->getVersionInfo() );

        $well = array(
            "latitude" => 43.756825,
            "longitude" => 15.775074
        );

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId( $contentType->id ),
                        new Criterion\MapLocationDistance(
                            "maplocation",
                            Criterion\Operator::GTE,
                            235,
                            $well["latitude"],
                            $well["longitude"]
                        )
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        "testtype",
                        "maplocation",
                        $well["latitude"],
                        $well["longitude"],
                        LocationQuery::SORT_DESC
                    )
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 3, $result->totalCount );
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group maplocation
     */
    public function testMapLocationDistanceWithCustomField()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft( $contentType );
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.894877,
                "longitude" => 15.972699,
                "address" => "Here be wild boars",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $wildBoars = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.927334,
                "longitude" => 15.934847,
                "address" => "A lone tree",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $tree = $contentService->publishVersion( $draft->getVersionInfo() );

        $distanceCriterion = new Criterion\MapLocationDistance(
            "maplocation",
            Criterion\Operator::LTE,
            240,
            43.756825,
            15.775074
        );
        $distanceCriterion->setCustomField( 'testtype', 'maplocation', 'custom_geolocation_field' );

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId( $contentType->id ),
                        $distanceCriterion
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array()
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 1, $result->totalCount );
        $this->assertEquals(
            $wildBoars->contentInfo->mainLocationId,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findLocations() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findLocations()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group maplocation
     */
    public function testMapLocationDistanceWithCustomFieldSort()
    {
        $contentType = $this->createTestPlaceContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $contentTypeService->createContentTypeDraft( $contentType );
        $locationCreateStruct = $repository->getLocationService()->newLocationCreateStruct( 2 );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.894877,
                "longitude" => 15.972699,
                "address" => "Here be wild boars",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $wildBoars = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.927334,
                "longitude" => 15.934847,
                "address" => "A lone tree",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $tree = $contentService->publishVersion( $draft->getVersionInfo() );

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = "eng-GB";
        $createStruct->setField(
            "maplocation",
            array(
                "latitude" => 45.903777,
                "longitude" => 15.958788,
                "address" => "Meadow with mushrooms",
            ),
            "eng-GB"
        );

        $draft = $contentService->createContent( $createStruct, array( $locationCreateStruct ) );
        $mushrooms = $contentService->publishVersion( $draft->getVersionInfo() );

        $well = array(
            "latitude" => 43.756825,
            "longitude" => 15.775074
        );

        $sortClause = new SortClause\MapLocationDistance(
            "testtype",
            "maplocation",
            $well["latitude"],
            $well["longitude"],
            LocationQuery::SORT_DESC
        );
        $sortClause->setCustomField( 'testtype', 'maplocation', 'custom_geolocation_field' );

        $query = new LocationQuery(
            array(
                'filter' => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeId( $contentType->id ),
                        new Criterion\MapLocationDistance(
                            "maplocation",
                            Criterion\Operator::GTE,
                            235,
                            $well["latitude"],
                            $well["longitude"]
                        )
                    )
                ),
                'offset' => 0,
                'limit' => 10,
                'sortClauses' => array(
                    $sortClause
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findLocations( $query );

        $this->assertEquals( 3, $result->totalCount );
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
     * Assert that query result matches the given fixture.
     *
     * @param LocationQuery $query
     * @param string $fixture
     * @param null|callable $closure
     *
     * @return void
     */
    protected function assertQueryFixture( LocationQuery $query, $fixture, $closure = null )
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        try
        {
            $result = $searchService->findLocations( $query );
            $this->simplifySearchResult( $result );
        }
        catch ( NotImplementedException $e )
        {
            $this->markTestSkipped(
                "This feature is not supported by the current search backend: " . $e->getMessage()
            );
        }

        if ( !is_file( $fixture ) )
        {
            if ( isset( $_ENV['ez_tests_record'] ) )
            {
                file_put_contents(
                    $record = $fixture . '.recording',
                    "<?php\n\nreturn " . var_export( $result, true ) . ";\n\n"
                );
                $this->markTestIncomplete( "No fixture available. Result recorded at $record. Result: \n" . $this->printResult( $result ) );
            }
            else
            {
                $this->markTestIncomplete( "No fixture available. Set \$_ENV['ez_tests_record'] to generate it." );
            }
        }

        if ( $closure !== null )
        {
            $closure( $result );
        }

        $this->assertEquals(
            include $fixture,
            $result,
            "Search results do not match.",
            .1 // Be quite generous regarding delay -- most important for scores
        );
    }

    /**
     * Show a simplified view of the search result for manual introspection
     *
     * @param SearchResult $result
     *
     * @return string
     */
    protected function printResult( SearchResult $result )
    {
        $printed = '';
        foreach ( $result->searchHits as $hit )
        {
            $printed .= sprintf( " - %s (%s)\n", $hit->valueObject['title'], $hit->valueObject['id'] );
        }
        return $printed;
    }

    /**
     * Simplify search result
     *
     * This leads to saner comparisons of results, since we do not get the full
     * content objects every time.
     *
     * @param SearchResult $result
     *
     * @return void
     */
    protected function simplifySearchResult( SearchResult $result )
    {
        $result->time = 1;

        foreach ( $result->searchHits as $hit )
        {
            switch ( true )
            {
                case $hit->valueObject instanceof Location:
                    $hit->valueObject = array(
                        'id' => $hit->valueObject->contentInfo->id,
                        'title' => $hit->valueObject->contentInfo->name,
                    );
                    break;

                default:
                    throw new \RuntimeException( "Unknown search result hit type: " . get_class( $hit->valueObject ) );
            }
        }
    }

    /**
     * Get fixture directory
     *
     * @return string
     */
    protected function getFixtureDir()
    {
        return __DIR__ . '/_fixtures/' . getenv( "fixtureDir" ) . '/';
    }
}
