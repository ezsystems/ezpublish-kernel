<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field as FieldSortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacySolr;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;

/**
 * Integration test for searching and sorting with Field criterion and Field sort clause.
 *
 * This abstract test case should be used as a base for a specific field type search
 * integration tests. It will first create two Content objects with two distinct field
 * values, then execute a series of tests for Field criterion and Field sort clause,
 * explicitly limited on these two values.
 *
 * Field criterion will be tested for each supported operator in all possible ways,
 * combined with LogicalNot criterion, while Field sort clause will be tested in
 * ascending and descending order.
 *
 * Same set of tests will be executed for each type of search (Content search, Location
 * search), and in case of a criterion separately for a filtering and querying type of
 * Query.
 *
 * To get the test working extend it in a concrete field type test and implement
 * methods:
 *
 * - getValidSearchValueOne()
 * - getValidSearchValueTwo()
 *
 * In the test descriptions Content object created with values One and Two are referred to
 * as Content One, and Content Two. See the descriptions of the abstract declarations of
 * these methods for more details on how to choose proper values.
 *
 * Note: this test case does not concern itself with testing field filters, behaviour
 * of multiple sort clauses or combination with other criteria. These are tested
 * elsewhere as a general field search cases, which enables keeping this test case
 * simple.
 */
abstract class SearchBaseIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get search field value One.
     *
     * The value must be valid for Content creation.
     *
     * When Field sort clause with ascending order is used on the tested field,
     * Content containing the field with this value must come before the Content
     * with value One.
     *
     * Opposite should be the case when using descending order.
     *
     * @return mixed
     */
    abstract protected function getValidSearchValueOne();

    /**
     * Get search field value Two.
     *
     * The value must be valid for Content creation.
     *
     * When Field sort clause with ascending order is used on the tested field,
     * Content containing the field with this value must come after the Content
     * with value One.
     *
     * Opposite should be the case when using descending order.
     *
     * @return mixed
     */
    abstract protected function getValidSearchValueTwo();

    /**
     * Creates and returns content with given $fieldData
     *
     * @param mixed $fieldData
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestSearchContent( $fieldData, Repository $repository, $contentType )
    {
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $createStruct = $contentService->newContentCreateStruct( $contentType, "eng-US" );
        $createStruct->setField( "name", "Test object" );
        $createStruct->setField(
            "data",
            $fieldData
        );

        $locationCreateStruct = $locationService->newLocationCreateStruct( 2 );

        return $contentService->publishVersion(
            $contentService->createContent(
                $createStruct,
                array(
                    $locationCreateStruct,
                )
            )->versionInfo
        );
    }

    public function criteriaProvider()
    {
        return array(
            0 => array(
                // Tests search with EQ operator.
                //
                // Simplified representation:
                //
                //     value EQ One
                //
                // The result should contain Content One.
                new Field( "data", Operator::EQ, $this->getValidSearchValueOne() ),
                true,
                false,
            ),
            1 => array(
                // Tests search with EQ operator.
                //
                // Simplified representation:
                //
                //     NOT( value EQ One )
                //
                // The result should contain Content Two.
                new LogicalNot( new Field( "data", Operator::EQ, $this->getValidSearchValueOne() ) ),
                false,
                true,
            ),
            2 => array(
                // Tests search with EQ operator.
                //
                // Simplified representation:
                //
                //     value EQ Two
                //
                // The result should contain Content Two.
                new Field( "data", Operator::EQ, $this->getValidSearchValueTwo() ),
                false,
                true,
            ),
            3 => array(
                // Tests search with EQ operator.
                //
                // Simplified representation:
                //
                //     NOT( value EQ Two )
                //
                // The result should contain Content One.
                new LogicalNot( new Field( "data", Operator::EQ, $this->getValidSearchValueTwo() ) ),
                true,
                false,
            ),
            4 => array(
                // Tests search with IN operator.
                //
                // Simplified representation:
                //
                //     value IN [One]
                //
                // The result should contain Content One.
                new Field( "data", Operator::IN, array( $this->getValidSearchValueOne() ) ),
                true,
                false,
            ),
            5 => array(
                // Tests search with IN operator.
                //
                // Simplified representation:
                //
                //     NOT( value IN [One] )
                //
                // The result should contain Content Two.
                new LogicalNot(
                    new Field( "data", Operator::IN, array( $this->getValidSearchValueOne() ) )
                ),
                false,
                true,
            ),
            6 => array(
                // Tests search with IN operator.
                //
                // Simplified representation:
                //
                //     value IN [Two]
                //
                // The result should contain Content Two.
                new Field( "data", Operator::IN, array( $this->getValidSearchValueTwo() ) ),
                false,
                true,
            ),
            7 => array(
                // Tests search with IN operator.
                //
                // Simplified representation:
                //
                //     NOT( value IN [Two] )
                //
                // The result should contain Content One.
                new LogicalNot(
                    new Field( "data", Operator::IN, array( $this->getValidSearchValueTwo() ) )
                ),
                true,
                false,
            ),
            8 => array(
                // Tests search with IN operator.
                //
                // Simplified representation:
                //
                //     value IN [One,Two]
                //
                // The result should contain both Content One and Content Two.
                new Field(
                    "data",
                    Operator::IN,
                    array(
                        $this->getValidSearchValueOne(),
                        $this->getValidSearchValueTwo(),
                    )
                ),
                true,
                true,
            ),
            9 => array(
                // Tests search with IN operator.
                //
                // Simplified representation:
                //
                //     NOT( value IN [One,Two] )
                //
                // The result should be empty.
                new LogicalNot(
                    new Field(
                        "data",
                        Operator::IN,
                        array(
                            $this->getValidSearchValueOne(),
                            $this->getValidSearchValueTwo(),
                        )
                    )
                ),
                false,
                false,
            ),
            10 => array(
                // Tests search with GT operator.
                //
                // Simplified representation:
                //
                //     value GT One
                //
                // The result should contain Content Two.
                new Field( "data", Operator::GT, $this->getValidSearchValueOne() ),
                false,
                true,
            ),
            11 => array(
                // Tests search with GT operator.
                //
                // Simplified representation:
                //
                //     NOT( value GT One )
                //
                // The result should contain Content One.
                new LogicalNot( new Field( "data", Operator::GT, $this->getValidSearchValueOne() ) ),
                true,
                false,
            ),
            12 => array(
                // Tests search with GT operator.
                //
                // Simplified representation:
                //
                //     value GT Two
                //
                // The result should be empty.
                new Field( "data", Operator::GT, $this->getValidSearchValueTwo() ),
                false,
                false,
            ),
            13 => array(
                // Tests search with GT operator.
                //
                // Simplified representation:
                //
                //     NOT( value GT Two )
                //
                // The result should contain both Content One and Content Two.
                new LogicalNot( new Field( "data", Operator::GT, $this->getValidSearchValueTwo() ) ),
                true,
                true,
            ),
            14 => array(
                // Tests search with GTE operator.
                //
                // Simplified representation:
                //
                //     value GTE One
                //
                // The result should contain both Content One and Content Two.
                new Field( "data", Operator::GTE, $this->getValidSearchValueOne() ),
                true,
                true,
            ),
            15 => array(
                // Tests search with GTE operator.
                //
                // Simplified representation:
                //
                //     NOT( value GTE One )
                //
                // The result should be empty.
                new LogicalNot( new Field( "data", Operator::GTE, $this->getValidSearchValueOne() ) ),
                false,
                false,
            ),
            16 => array(
                // Tests search with GTE operator.
                //
                // Simplified representation:
                //
                //     value GTE Two
                //
                // The result should contain Content Two.
                new Field( "data", Operator::GTE, $this->getValidSearchValueTwo() ),
                false,
                true,
            ),
            17 => array(
                // Tests search with GTE operator.
                //
                // Simplified representation:
                //
                //     NOT( value GTE Two )
                //
                // The result should contain Content One.
                new LogicalNot( new Field( "data", Operator::GTE, $this->getValidSearchValueTwo() ) ),
                true,
                false,
            ),
            18 => array(
                // Tests search with LT operator.
                //
                // Simplified representation:
                //
                //     value LT One
                //
                // The result should be empty.
                new Field( "data", Operator::LT, $this->getValidSearchValueOne() ),
                false,
                false,
            ),
            19 => array(
                // Tests search with LT operator.
                //
                // Simplified representation:
                //
                //     NOT( value LT One )
                //
                // The result should contain both Content One and Content Two.
                new LogicalNot( new Field( "data", Operator::LT, $this->getValidSearchValueOne() ) ),
                true,
                true,
            ),
            20 => array(
                // Tests search with LT operator.
                //
                // Simplified representation:
                //
                //     value LT Two
                //
                // The result should contain Content One.
                new Field( "data", Operator::LT, $this->getValidSearchValueTwo() ),
                true,
                false,
            ),
            21 => array(
                // Tests search with LT operator.
                //
                // Simplified representation:
                //
                //     NOT( value LT Two )
                //
                // The result should contain Content Two.
                new LogicalNot( new Field( "data", Operator::LT, $this->getValidSearchValueTwo() ) ),
                false,
                true,
            ),
            22 => array(
                // Tests search with LTE operator.
                //
                // Simplified representation:
                //
                //     value LTE One
                //
                // The result should contain Content One.
                new Field( "data", Operator::LTE, $this->getValidSearchValueOne() ),
                true,
                false,
            ),
            23 => array(
                // Tests search with LTE operator.
                //
                // Simplified representation:
                //
                //     NOT( value LTE One )
                //
                // The result should contain Content Two.
                new LogicalNot( new Field( "data", Operator::LTE, $this->getValidSearchValueOne() ) ),
                false,
                true,
            ),
            24 => array(
                // Tests search with LTE operator.
                //
                // Simplified representation:
                //
                //     value LTE Two
                //
                // The result should contain both Content One and Content Two.
                new Field( "data", Operator::LTE, $this->getValidSearchValueTwo() ),
                true,
                true,
            ),
            25 => array(
                // Tests search with LTE operator.
                //
                // Simplified representation:
                //
                //     NOT( value LTE Two )
                //
                // The result should be empty.
                new LogicalNot( new Field( "data", Operator::LTE, $this->getValidSearchValueTwo() ) ),
                false,
                false,
            ),
            26 => array(
                // Tests search with BETWEEN operator.
                //
                // Simplified representation:
                //
                //     value BETWEEN [One,Two]
                //
                // The result should contain both Content One and Content Two.
                new Field(
                    "data",
                    Operator::BETWEEN,
                    array(
                        $this->getValidSearchValueOne(),
                        $this->getValidSearchValueTwo(),
                    )
                ),
                true,
                true,
            ),
            27 => array(
                // Tests search with BETWEEN operator.
                //
                // Simplified representation:
                //
                //     NOT( value BETWEEN [One,Two] )
                //
                // The result should contain both Content One and Content Two.
                new LogicalNot(
                    new Field(
                        "data",
                        Operator::BETWEEN,
                        array(
                            $this->getValidSearchValueOne(),
                            $this->getValidSearchValueTwo(),
                        )
                    )
                ),
                false,
                false,
            ),
            28 => array(
                // Tests search with BETWEEN operator.
                //
                // Simplified representation:
                //
                //     value BETWEEN [Two,One]
                //
                // The result should be empty.
                new Field(
                    "data",
                    Operator::BETWEEN,
                    array(
                        $this->getValidSearchValueTwo(),
                        $this->getValidSearchValueOne(),
                    )
                ),
                false,
                false,
            ),
            29 => array(
                // Tests search with BETWEEN operator.
                //
                // Simplified representation:
                //
                //     NOT( value BETWEEN [Two,One] )
                //
                // The result should contain both Content One and Content Two.
                new LogicalNot(
                    new Field(
                        "data",
                        Operator::BETWEEN,
                        array(
                            $this->getValidSearchValueTwo(),
                            $this->getValidSearchValueOne(),
                        )
                    )
                ),
                true,
                true,
            ),
            30 => array(
                // Tests search with CONTAINS operator.
                //
                // Simplified representation:
                //
                //     value CONTAINS One
                //
                // The result should contain Content One.
                new Field( "data", Operator::CONTAINS, $this->getValidSearchValueOne() ),
                true,
                false,
            ),
            31 => array(
                // Tests search with CONTAINS operator.
                //
                // Simplified representation:
                //
                //     NOT( value CONTAINS One )
                //
                // The result should contain Content Two.
                new LogicalNot( new Field( "data", Operator::CONTAINS, $this->getValidSearchValueOne() ) ),
                false,
                true,
            ),
            32 => array(
                // Tests search with CONTAINS operator.
                //
                // Simplified representation:
                //
                //     value CONTAINS Two
                //
                // The result should contain Content Two.
                new Field( "data", Operator::CONTAINS, $this->getValidSearchValueTwo() ),
                false,
                true,
            ),
            33 => array(
                // Tests search with CONTAINS operator.
                //
                // Simplified representation:
                //
                //     NOT( value CONTAINS Two )
                //
                // The result should contain Content One.
                new LogicalNot(
                    new Field( "data", Operator::CONTAINS, $this->getValidSearchValueTwo() )
                ),
                true,
                false,
            ),
        );
    }

    public function sortClauseProvider()
    {
        return array(
            0 => array(
                new FieldSortClause(
                    "test-" . $this->getTypeName(),
                    "data",
                    Query::SORT_ASC
                ),
                true
            ),
            1 => array(
                new FieldSortClause(
                    "test-" . $this->getTypeName(),
                    "data",
                    Query::SORT_DESC
                ),
                false
            ),
        );
    }

    /**
     * Creates test Content and Locations and returns the context for subsequent testing
     *
     * Context consists of repository instance and created Content IDs.
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function testCreateTestContent()
    {
        $repository = $this->getRepository();
        $fieldTypeService = $repository->getFieldTypeService();
        $fieldType = $fieldTypeService->getFieldType( $this->getTypeName() );

        if ( !$fieldType->isSearchable() )
        {
            $this->markTestSkipped( "Field type '{$this->getTypeName()}' is not searchable." );
        }

        $contentType = $this->testCreateContentType();

        return array(
            $repository,
            $this->createTestSearchContent(
                $this->getValidSearchValueOne(),
                $repository,
                $contentType
            )->id,
            $this->createTestSearchContent(
                $this->getValidSearchValueTwo(),
                $repository,
                $contentType
            )->id,
        );
    }

    /**
     * Tests Content Search querying with Field criterion on a field of specific field type
     *
     * @dataProvider criteriaProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testFilterContent(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        list( $repository, $contentOneId, $contentTwoId ) = $context;
        $searchResult = $this->findContent( $repository, $criterion, true );

        $this->assertFindResult( $searchResult, $includesOne, $includesTwo, $contentOneId, $contentTwoId );
    }

    /**
     * Tests Content Search querying with Field criterion on a field of specific field type
     *
     * @dataProvider criteriaProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testQueryContent(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        list( $repository, $contentOneId, $contentTwoId ) = $context;
        $searchResult = $this->findContent( $repository, $criterion, false );

        $this->assertFindResult( $searchResult, $includesOne, $includesTwo, $contentOneId, $contentTwoId );
    }

    /**
     * Tests Content Search sort with Field sort clause on a field of specific field type
     *
     * @dataProvider sortClauseProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     * @param boolean $ascending
     * @param array $context
     */
    public function testSortContent( SortClause $sortClause, $ascending, array $context )
    {
        $setupFactory = $this->getSetupFactory();

        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped(
                "For Solr engine fields are not searchable with Location Search"
            );
        }

        list( $repository, $contentOneId, $contentTwoId ) = $context;
        $searchResult = $this->sortContent( $repository, $sortClause );

        $this->assertSortResult( $searchResult, $ascending, $contentOneId, $contentTwoId );
    }

    /**
     * Tests Location Search filtering with Field criterion on a field of specific field type
     *
     * @dataProvider criteriaProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testFilterLocations(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        $setupFactory = $this->getSetupFactory();

        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped(
                "For Solr engine fields are not searchable with Location Search"
            );
        }

        if ( $setupFactory instanceof LegacyElasticsearch )
        {
            $this->markTestSkipped(
                "For Elasticsearch engine fields are not searchable with Location Search"
            );
        }

        list( $repository, $contentOneId, $contentTwoId ) = $context;
        $searchResult = $this->findLocations( $repository, $criterion, true );

        $this->assertFindResult( $searchResult, $includesOne, $includesTwo, $contentOneId, $contentTwoId );
    }

    /**
     * Tests Location Search querying with Field criterion on a field of specific field type
     *
     * @dataProvider criteriaProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param array $context
     */
    public function testQueryLocations(
        Criterion $criterion,
        $includesOne,
        $includesTwo,
        array $context
    )
    {
        $setupFactory = $this->getSetupFactory();

        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped(
                "For Solr engine fields are not searchable with Location Search"
            );
        }

        if ( $setupFactory instanceof LegacyElasticsearch )
        {
            $this->markTestSkipped(
                "For Elasticsearch engine fields are not searchable with Location Search"
            );
        }

        list( $repository, $contentOneId, $contentTwoId ) = $context;
        $searchResult = $this->findLocations( $repository, $criterion, false );

        $this->assertFindResult( $searchResult, $includesOne, $includesTwo, $contentOneId, $contentTwoId );
    }

    /**
     * Tests Location Search sort with Field sort clause on a field of specific field type
     *
     * @dataProvider sortClauseProvider
     * @depends testCreateTestContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     * @param boolean $ascending
     * @param array $context
     */
    public function testSortLocations( SortClause $sortClause, $ascending, array $context )
    {
        $setupFactory = $this->getSetupFactory();

        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped(
                "For Solr engine fields are not searchable with Location Search"
            );
        }

        if ( $setupFactory instanceof LegacyElasticsearch )
        {
            $this->markTestSkipped(
                "For Elasticsearch engine fields are not searchable with Location Search"
            );
        }

        list( $repository, $contentOneId, $contentTwoId ) = $context;
        $searchResult = $this->sortLocations( $repository, $sortClause );

        $this->assertSortResult( $searchResult, $ascending, $contentOneId, $contentTwoId );
    }

    /**
     * Returns SearchResult of the tested Content for the given $criterion
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $filter Denotes search by filtering if true, search by querying if false
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function findContent( Repository $repository, Criterion $criterion, $filter )
    {
        $searchService = $repository->getSearchService();

        if ( $filter )
        {
            $criteriaProperty = "filter";
        }
        else
        {
            $criteriaProperty = "query";
        }

        $query = new Query(
            array(
                $criteriaProperty => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeIdentifier( "test-" . $this->getTypeName() ),
                        $criterion,
                    )
                ),
            )
        );

        return $searchService->findContent( $query );
    }

    /**
     * Returns SearchResult of the tested Content for the given $sortClause
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function sortContent( Repository $repository, SortClause $sortClause )
    {
        $searchService = $repository->getSearchService();

        $query = new Query(
            array(
                "filter" => new Criterion\ContentTypeIdentifier( "test-" . $this->getTypeName() ),
                "sortClauses" => array(
                    $sortClause,
                ),
            )
        );

        return $searchService->findContent( $query );
    }

    /**
     * Returns SearchResult of the tested Locations for the given $criterion
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param boolean $filter Denotes search by filtering if true, search by querying if false
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function findLocations( Repository $repository, Criterion $criterion, $filter )
    {
        $searchService = $repository->getSearchService();

        if ( $filter )
        {
            $criteriaProperty = "filter";
        }
        else
        {
            $criteriaProperty = "query";
        }

        $query = new LocationQuery(
            array(
                $criteriaProperty => new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentTypeIdentifier( "test-" . $this->getTypeName() ),
                        $criterion,
                    )
                ),
            )
        );

        return $searchService->findContent( $query );
    }

    /**
     * Returns SearchResult of the tested Locations for the given $sortClause
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function sortLocations( Repository $repository, SortClause $sortClause )
    {
        $searchService = $repository->getSearchService();

        $query = new LocationQuery(
            array(
                "filter" => new Criterion\ContentTypeIdentifier( "test-" . $this->getTypeName() ),
                "sortClauses" => array(
                    $sortClause,
                ),
            )
        );

        return $searchService->findLocations( $query );
    }

    /**
     * Returns a list of Content IDs from given $searchResult, with order preserved
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     *
     * @return array
     */
    protected function getResultContentIdList( SearchResult $searchResult )
    {
        $contentIdList = array();

        foreach ( $searchResult->searchHits as $searchHit )
        {
            $valueObject = $searchHit->valueObject;

            switch ( true )
            {
                case $valueObject instanceof Content:
                    $contentIdList[] = $valueObject->id;
                    break;

                case $valueObject instanceof Location:
                    $contentIdList[] = $valueObject->contentId;
                    break;

                default:
                    throw new \RuntimeException(
                        "Unknown search result hit type: " . get_class( $searchHit->valueObject )
                    );
            }
        }

        return $contentIdList;
    }

    /**
     * Asserts expected result, deliberately ignoring order.
     *
     * Search result can be empty, contain both Content One and Content Two or only one of them.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     * @param boolean $includesOne
     * @param boolean $includesTwo
     * @param string|int $contentOneId
     * @param string|int $contentTwoId
     */
    protected function assertFindResult(
        SearchResult $searchResult,
        $includesOne,
        $includesTwo,
        $contentOneId,
        $contentTwoId
    )
    {
        $contentIdList = $this->getResultContentIdList( $searchResult );

        if ( $includesOne && $includesTwo )
        {
            $this->assertEquals( 2, $searchResult->totalCount );
            $this->assertNotEquals( $contentIdList[0], $contentIdList[1] );

            $this->assertThat(
                $contentIdList[0],
                $this->logicalOr( $this->equalTo( $contentOneId ), $this->equalTo( $contentTwoId ) )
            );

            $this->assertThat(
                $contentIdList[1],
                $this->logicalOr( $this->equalTo( $contentOneId ), $this->equalTo( $contentTwoId ) )
            );
        }
        else if ( !$includesOne && !$includesTwo )
        {
            $this->assertEquals( 0, $searchResult->totalCount );
        }
        else
        {
            $this->assertEquals( 1, $searchResult->totalCount );

            if ( $includesOne )
            {
                $this->assertEquals( $contentOneId, $contentIdList[0] );
            }

            if ( $includesTwo )
            {
                $this->assertEquals( $contentTwoId, $contentIdList[0] );
            }
        }
    }

    /**
     * Asserts order of the given $searchResult, both Content One and Two are always expected.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $searchResult
     * @param boolean $ascending Denotes ascending order if true, descending order if false
     * @param string|int $contentOneId
     * @param string|int $contentTwoId
     */
    protected function assertSortResult(
        SearchResult $searchResult,
        $ascending,
        $contentOneId,
        $contentTwoId
    )
    {
        $contentIdList = $this->getResultContentIdList( $searchResult );

        $indexOne = 0;
        $indexTwo = 1;

        if ( !$ascending )
        {
            $indexOne = 1;
            $indexTwo = 0;
        }

        $this->assertEquals( $contentOneId, $contentIdList[$indexOne] );
        $this->assertEquals( $contentTwoId, $contentIdList[$indexTwo] );
    }
}
