<?php
/**
 * File containing the ElasticsearchTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Test case for operations in the SearchService using Elasticsearch storage.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class ElasticsearchTest extends BaseTest
{
    public function providerForTestFilter()
    {
        $fixtureDir = $this->getFixtureDir();

        return array(
            array(
                array(
                    'filter' => new Criterion\ContentId(
                        array( 1, 4, 10 )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'ContentId.php',
            ),
            array(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentId(
                                array( 1, 4, 10 )
                            ),
                            new Criterion\ContentId(
                                array( 4, 12 )
                            ),
                        )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'LogicalAnd.php',
            ),
            array(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentId(
                                array( 1, 4, 10 )
                            ),
                            new Criterion\LogicalNot(
                                new Criterion\ContentId(
                                    array( 10, 12 )
                                )
                            ),
                        )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'LogicalNot.php',
            ),
            array(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\ContentId(
                                array( 1, 4, 10 )
                            ),
                            new Criterion\LogicalAnd(
                                array(
                                    new Criterion\LogicalNot(
                                        new Criterion\ContentId(
                                            array( 10, 12 )
                                        )
                                    ),
                                )
                            ),
                        )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'LogicalNot.php',
            ),
            array(
                array(
                    'filter' => new Criterion\LogicalOr(
                        array(
                            new Criterion\ContentId(
                                array( 1, 4, 10 )
                            ),
                            new Criterion\ContentId(
                                array( 4, 12 )
                            ),
                        )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'LogicalOr.php',
            ),
            array(
                array(
                    'filter' => new Criterion\LogicalOr(
                        array(
                            new Criterion\ContentId(
                                array( 1, 4, 10 )
                            ),
                            new Criterion\ContentId(
                                array( 4, 12 )
                            ),
                        )
                    ),
                    'sortClauses' => array( new SortClause\ContentId( Query::SORT_DESC ) )
                ),
                $fixtureDir . 'SortContentId.php',
            ),
            array(
                array(
                    'filter' => new Criterion\ContentTypeId(
                        4
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'ContentTypeId.php',
            ),
            array(
                array(
                    'filter' => new Criterion\Field(
                        'name',
                        Criterion\Operator::EQ,
                        'Members'
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'Field.php',
            ),
            array(
                array(
                    'filter' => new Criterion\Field(
                        'name',
                        Criterion\Operator::IN,
                        array( 'Members', 'Anonymous Users' )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                ),
                $fixtureDir . 'FieldIn.php',
            ),
        );
    }

    /**
     * @dataProvider providerForTestFilter
     */
    public function testFilterContent( $queryData, $fixture, $closure = null )
    {
        $query = new Query( $queryData );
        $this->assertQueryFixture( $query, $fixture, $closure );
    }

    /**
     * @dataProvider providerForTestFilter
     */
    public function testFilterLocations( $queryData, $fixture, $closure = null )
    {
        $query = new LocationQuery( $queryData );
        $this->assertQueryFixture( $query, $fixture, $closure );
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

        $draft = $contentService->createContent(
            $createStruct
        );
        $content = $contentService->publishVersion( $draft->getVersionInfo() );

        return $content;
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testMultilingualFieldSort()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", Query::SORT_DESC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer", Query::SORT_ASC, "ger-DE" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

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
            $this->mapResultContentIds( $result )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testMultilingualFieldSortVariant2()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", Query::SORT_ASC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer", Query::SORT_DESC, "ger-DE" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

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
            $this->mapResultContentIds( $result )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testMultilingualFieldSortVariant3()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", Query::SORT_DESC, "ger-DE" ),
                    new SortClause\Field( "test-type", "integer", Query::SORT_ASC, "eng-GB" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

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
            $this->mapResultContentIds( $result )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSearchWithFieldSortThrowsInvalidArgumentExceptionTranslatableField()
    {
        $contentType = $this->createTestContentType();
        $this->createMultilingualContent( $contentType, 1, 2 );

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", Query::SORT_ASC ),
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $searchService->findContent( $query );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSearchWithFieldSortThrowsInvalidArgumentExceptionNonTranslatableField()
    {
        $contentType = $this->createTestContentType();
        $this->createMultilingualContent( $contentType, 1, 2, 3, "eng-GB" );

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    // The main language can change, so no language code allowed on non-translatable field whatsoever
                    new SortClause\Field( "test-type", "integer2", Query::SORT_ASC, "eng-GB" ),
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $searchService->findContent( $query );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @group xxx
     */
    public function testMultilingualFieldSortWithNonTranslatableField()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "ger-DE" )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "ger-DE" )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", Query::SORT_DESC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer2", Query::SORT_ASC ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

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
            $this->mapResultContentIds( $result )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testMultilingualFieldSortWithDefaultLanguage()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "ger-DE" )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "ger-DE" )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer", Query::SORT_ASC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer2", Query::SORT_DESC ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

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
            $this->mapResultContentIds( $result )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testMultilingualFieldSortWithDefaultLanguageVariant2()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "eng-GB" )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "eng-GB" )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    new SortClause\Field( "test-type", "integer2", Query::SORT_DESC ),
                    new SortClause\Field( "test-type", "integer", Query::SORT_DESC, "ger-DE" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

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
            $this->mapResultContentIds( $result )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     */
    public function testMultilingualFieldSortUnusedLanguageDoesNotFilterResultSet()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2 )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4 )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3 )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1 )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    // "test-type" Content instance do not exist in "eng-US"
                    new SortClause\Field( "test-type", "integer", Query::SORT_ASC, "eng-US" ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

        $this->assertEquals( 4, $result->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\ElasticsearchTest::testMultilingualFieldSortUnusedLanguageDoesNotFilterResultSet
     */
    public function testMultilingualFieldSortUnusedLanguageDoesNotChangeSort()
    {
        $contentType = $this->createTestContentType();

        // Create a draft to account for behaviour with ContentType in different states
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeService->createContentTypeDraft( $contentType );

        $contentIdList = array();
        $contentIdList[1] = $this->createMultilingualContent( $contentType, 1, 2, 1, "eng-GB" )->id;
        $contentIdList[2] = $this->createMultilingualContent( $contentType, 2, 4, 3, "eng-GB" )->id;
        $contentIdList[3] = $this->createMultilingualContent( $contentType, 2, 3, 4, "ger-DE" )->id;
        $contentIdList[4] = $this->createMultilingualContent( $contentType, 1, 1, 2, "ger-DE" )->id;

        $query = new Query(
            array(
                'criterion' => new Criterion\ContentTypeId( $contentType->id ),
                'sortClauses' => array(
                    // "test-type" Content instance do not exist in "eng-US"
                    new SortClause\Field( "test-type", "integer", Query::SORT_DESC, "eng-US" ),
                    new SortClause\Field( "test-type", "integer", Query::SORT_ASC, "eng-GB" ),
                    new SortClause\Field( "test-type", "integer2", Query::SORT_ASC ),
                )
            )
        );

        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

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
            $this->mapResultContentIds( $result )
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $result
     *
     * @return array
     */
    protected function mapResultContentIds( SearchResult $result )
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
     * Assert that query result matches the given fixture.
     *
     * @param Query $query
     * @param string $fixture
     * @param null|callable $closure
     *
     * @return void
     */
    protected function assertQueryFixture( Query $query, $fixture, $closure = null )
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        /** @var $result */
        try
        {
            if ( $query instanceof LocationQuery )
            {
                $result = $searchService->findLocations( $query );
            }
            else if ( $query instanceof Query )
            {
                $result = $searchService->findContent( $query );
            }
            else
            {
                $this->fail( "Expected instance of LocationQuery or Query, got: " . gettype( $query ) );
            }
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
                case $hit->valueObject instanceof Content:
                    $hit->valueObject = array(
                        'id'    => $hit->valueObject->contentInfo->id,
                        'title' => $hit->valueObject->contentInfo->name,
                    );
                    break;

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
