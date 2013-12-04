<?php
/**
 * File containing the SearchServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacySolr;

/**
 * Test case for operations in the SearchService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class SearchServiceTest extends BaseTest
{
    public function getFilterSearches()
    {
        $fixtureDir = $this->getFixtureDir();
        return array(
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\ContentId(
                            array( 1, 4, 10 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'ContentId.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\LogicalAnd(
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
                    )
                ),
                $fixtureDir . 'LogicalAnd.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\LogicalOr(
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
                    )
                ),
                $fixtureDir . 'LogicalOr.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\LogicalAnd(
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
                    )
                ),
                $fixtureDir . 'LogicalNot.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\Subtree(
                            '/1/5/'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'Subtree.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\ContentTypeId(
                            4
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'ContentTypeId.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\ContentTypeGroupId(
                            2
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'ContentTypeGroupId.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GT,
                            1343140540
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DateMetadataGt.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GTE,
                            1311154215
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DateMetadataGte.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::LTE,
                            1311154215
                        ),
                        'limit' => 10,
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DateMetadataLte.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::IN,
                            array( 1033920794, 1060695457, 1343140540 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DateMetadataIn.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::BETWEEN,
                            array( 1033920776, 1072180276 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DateMetadataBetween.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\DateMetadata(
                            Criterion\DateMetadata::CREATED,
                            Criterion\Operator::BETWEEN,
                            array( 1033920776, 1072180278 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DateMetadataCreated.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\LocationId(
                            array( 1, 2, 5 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'LocationId.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\ParentLocationId(
                            array( 1 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'ParentLocationId.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\RemoteId(
                            array( 'f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca' )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'RemoteId.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\LocationRemoteId(
                            array( '3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983' )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'LocationRemoteId.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\SectionId(
                            array( 2 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'SectionId.php',
            ),
            array(
                new Query(
                    array(
                        // There is no Status Criterion anymore, this should match all published as well
                        'filter' => new Criterion\Subtree(
                            '/1/'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'Status.php',
                // Result having the same sort level should be sorted between them to be system independent
                function ( &$data )
                {
                    usort(
                        $data->searchHits,
                        function ( $a, $b )
                        {
                            if ( $a->score == $b->score )
                            {
                                if ( $a->valueObject["id"] == $b->valueObject["id"] )
                                {
                                    return 0;
                                }

                                // Order by ascending ID
                                return ( $a->valueObject["id"] < $b->valueObject["id"] ) ? -1 : 1;
                            }

                            // Order by descending score
                            return ( $a->score > $b->score ) ? -1 : 1;
                        }
                    );
                }
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\Field(
                            'name',
                            Criterion\Operator::EQ,
                            'Members'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'Field.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\Field(
                            'name',
                            Criterion\Operator::IN,
                            array( 'Members', 'Anonymous Users' )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FieldIn.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::BETWEEN,
                            array( 1033920275, 1033920794 )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FieldBetween.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\LogicalOr(
                            array(
                                new Criterion\Field(
                                    'name',
                                    Criterion\Operator::EQ,
                                    'Members'
                                ),
                                new Criterion\DateMetadata(
                                    Criterion\DateMetadata::MODIFIED,
                                    Criterion\Operator::BETWEEN,
                                    array( 1033920275, 1033920794 )
                                ),
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FieldOr.php',
            ),
        );
    }

    public function getQuerySearches()
    {
        $fixtureDir = $this->getFixtureDir();
        return array(
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\ContentId(
                            array( 58, 10 )
                        ),
                        'query'    => new Criterion\FullText( 'contact' ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FullTextFiltered.php',
            ),
            array(
                new Query(
                    array(
                        'query'    => new Criterion\FullText(
                            'contact',
                            array(
                                'boost' => array(
                                    'title' => 2,
                                ),
                                'fuzzyness' => .5,
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FullText.php',
            ),
            array(
                new Query(
                    array(
                        'query'    => new Criterion\FullText(
                            'Contact*'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FullTextWildcard.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LanguageCode( "eng-GB" ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'LanguageCode.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LanguageCode( array( "eng-US", "eng-GB" ) ),
                        'offset' => 10,
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'LanguageCodeIn.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LanguageCode( "eng-GB", true ),
                        'offset' => 10,
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'LanguageCodeAlwaysAvailable.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Depth( Criterion\Operator::EQ, 1 ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'Depth.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Depth( Criterion\Operator::IN, array( 1, 3 ) ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DepthIn.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Depth( Criterion\Operator::GT, 2 ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DepthGt.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Depth( Criterion\Operator::GTE, 2 ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DepthGte.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Depth( Criterion\Operator::LT, 2 ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'Depth.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Depth( Criterion\Operator::LTE, 2 ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DepthLte.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Depth( Criterion\Operator::BETWEEN, array( 1, 2 ) ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'DepthLte.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\Visibility(
                            Criterion\Visibility::VISIBLE
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'Visibility.php',
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getFilterSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFindContentFiltered( Query $query, $fixture, $closure = null )
    {
        $this->assertQueryFixture( $query, $fixture, $closure );
    }

    /**
     * Test for deprecated $criterion property on query object
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @deprecated
     */
    public function testDeprecatedCriteriaProperty()
    {
        $this->assertQueryFixture(
            new Query(
                array(
                    'criterion' => new Criterion\ContentId(
                        array( 1, 4, 10 )
                    ),
                    'sortClauses' => array( new SortClause\ContentId() )
                )
            ),
            $this->getFixtureDir() . 'DeprecatedContentIdQuery.php'
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getQuerySearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testQueryContent( Query $query, $fixture, $closure = null )
    {
        $this->assertQueryFixture( $query, $fixture, $closure );
    }

    public function getCaseInsensitiveSearches()
    {
        return array(
            array(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::EQ,
                            'Members'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::EQ,
                            'members'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
            ),
            array(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::EQ,
                            'MEMBERS'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getCaseInsensitiveSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFiltersCaseSensitivity( Query $query )
    {
        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . 'Field.php'
        );
    }

    public function testFindSingle()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $content = $searchService->findSingle(
            new Criterion\ContentId(
                array( 4 )
            )
        );

        $this->assertEquals(
            4,
            $content->id
        );
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

        $draft = $contentService->createContent( $createStruct );
        $content = $contentService->publishVersion( $draft->getVersionInfo() );

        return $content;
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldCollectionContains()
    {
        $testContent = $this->createMultipleCountriesContent();

        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof \eZ\Publish\API\Repository\Tests\SetupFactory\LegacySolr )
        {
            $country = "BE";
        }
        else
        {
            $country = "Belgium";
        }

        $query = new Query(
            array(
                'criterion' => new Criterion\Field(
                    "countries",
                    Criterion\Operator::CONTAINS,
                    $country
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

        $this->assertEquals( 1, $result->totalCount );
        $this->assertEquals(
            $testContent->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testFieldCollectionContains
     */
    public function testFieldCollectionContainsNoMatch()
    {
        $this->createMultipleCountriesContent();
        $query = new Query(
            array(
                'criterion'   => new Criterion\Field(
                    "countries",
                    Criterion\Operator::CONTAINS,
                    "Netherlands Antilles"
                )
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( $query );

        $this->assertEquals( 0, $result->totalCount );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testInvalidFieldIdentifierRange()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'filter'    => new Criterion\Field(
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

        $searchService->findContent(
            new Query(
                array(
                    'filter'    => new Criterion\Field(
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
    public function testFindContentWithNonSearchableField()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'filter'    => new Criterion\Field(
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleFailMultiple()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findSingle(
            new Criterion\ContentId(
                array( 4, 10 )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleWithNonSearchableField()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findSingle(
            new Criterion\Field(
                'tag_cloud_url',
                Criterion\Operator::EQ,
                'http://nimbus.com'
            )
        );
    }

    public function getSortedSearches()
    {
        $fixtureDir = $this->getFixtureDir();
        return array(
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 2 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'sortClauses' => array()
                    )
                ),
                $fixtureDir . 'SortNone.php',
                // Result having the same sort level should be sorted between them to be system independent
                function ( &$data )
                {
                    usort(
                        $data->searchHits,
                        function ( $a, $b )
                        {
                            return ( $a->valueObject["id"] < $b->valueObject["id"] ) ? -1 : 1;
                        }
                    );
                },
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 2 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'sortClauses' => array( new SortClause\LocationPathString( Query::SORT_DESC ) )
                    )
                ),
                $fixtureDir . 'SortPathString.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 2 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'sortClauses' => array( new SortClause\LocationDepth( Query::SORT_ASC ) )
                    )
                ),
                $fixtureDir . 'SortLocationDepth.php',
                // Result having the same sort level should be sorted between them to be system independent
                function ( &$data )
                {
                    // Result with ids:
                    //     4 has depth = 1
                    //     11, 12, 13, 42, 59 have depth = 2
                    //     10, 14 have depth = 3
                    $map = array(
                        4 => 0,
                        11 => 1,
                        12 => 2,
                        13 => 3,
                        42 => 4,
                        59 => 5,
                        10 => 6,
                        14 => 7,
                    );
                    usort(
                        $data->searchHits,
                        function ( $a, $b ) use ( $map )
                        {
                            return ( $map[$a->valueObject["id"]] < $map[$b->valueObject["id"]] ) ? -1 : 1;
                        }
                    );
                },
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 3 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'sortClauses' => array(
                            new SortClause\LocationPathString( Query::SORT_DESC ),
                            new SortClause\ContentName( Query::SORT_ASC )
                        )
                    )
                ),
                $fixtureDir . 'SortMultiple.php',
            ),
            array(
                // FIXME: this test is not relevant since all priorities are "0"
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 2 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'sortClauses' => array(
                            new SortClause\LocationPriority( Query::SORT_DESC ),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortDesc.php',
                // Result having the same sort level should be sorted between them to be system independent
                // Update when above FIXME has been resolved.
                function ( &$data )
                {
                    usort(
                        $data->searchHits,
                        function ( $a, $b )
                        {
                            return ( $a->valueObject["id"] < $b->valueObject["id"] ) ? -1 : 1;
                        }
                    );
                },
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 2 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'sortClauses' => array(
                            new SortClause\DatePublished(),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortDatePublished.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 4, 2, 6, 3 ) ),
                        'offset'      => 0,
                        'limit'       => null,
                        'sortClauses' => array(
                            new SortClause\SectionIdentifier(),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortSectionIdentifier.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 4, 2, 6, 3 ) ),
                        'offset'      => 0,
                        'limit'       => null,
                        'sortClauses' => array(
                            new SortClause\SectionName(),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortSectionName.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 2, 3 ) ),
                        'offset'      => 0,
                        'limit'       => null,
                        'sortClauses' => array(
                            new SortClause\ContentName(),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortContentName.php',
            ),
            array(
                new Query(
                    array(
                        'filter'    => new Criterion\ContentTypeId( 1 ),
                        'offset'      => 0,
                        'limit'       => null,
                        'sortClauses' => array(
                            new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-US" ),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortFolderName.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 5 ) ),
                        'offset'      => 0,
                        'limit'       => null,
                        'sortClauses' => array(
                            new SortClause\Field( "template_look", "title", Query::SORT_ASC ),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortTemplateTitle.php',
            ),
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

        $draft = $contentService->createContent( $createStruct );
        $content = $contentService->publishVersion( $draft->getVersionInfo() );

        return $content;
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSort()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortVariant2()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depe_nds eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @group asdf
     */
    public function testMultilingualFieldSortVariant3()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSearchWithFieldSortThrowsInvalidArgumentExceptionTranslatableField()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSearchWithFieldSortThrowsInvalidArgumentExceptionNonTranslatableField()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortWithNonTranslatableField()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortWithDefaultLanguage()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortWithDefaultLanguageVariant2()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMultilingualFieldSortUnusedLanguageDoesNotFilterResultSet()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testMultilingualFieldSortUnusedLanguageDoesNotFilterResultSet
     */
    public function testMultilingualFieldSortUnusedLanguageDoesNotChangeSort()
    {
        $setupFactory = $this->getSetupFactory();
        if ( $setupFactory instanceof LegacySolr )
        {
            $this->markTestSkipped( "Field SortClause is not yet implemented for Solr storage" );
        }

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
     * Test for the findContent() method.
     *
     * @dataProvider getSortedSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFindAndSortContent( Query $query, $fixture, $closure = null )
    {
        $this->assertQueryFixture( $query, $fixture, $closure );
    }

    public function getFacettedSearches()
    {
        $fixtureDir = $this->getFixtureDir();
        return array(
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\ContentTypeFacetBuilder()
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetContentType.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\ContentTypeFacetBuilder(
                                array(
                                    'minCount' => 3,
                                )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetContentTypeMinCount.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\ContentTypeFacetBuilder(
                                array(
                                    'limit' => 5,
                                )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetContentTypeMinLimit.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\SectionFacetBuilder()
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetSection.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\UserFacetBuilder()
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetUser.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\TermFacetBuilder()
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetTerm.php',
            ),
            /* @todo: It needs to be defined how this one is supposed to work.
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\CriterionFacetBuilder()
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetCriterion.php',
            ), // */
            /* @todo: Add sane ranges here:
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\DateRangeFacetBuilder( array() )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetDateRange.php',
            ), // */
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array( 'article/title' ),
                                )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetFieldSimple.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array( 'article/title' ),
                                    'regex'      => '(a|b|c)',
                                )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetFieldRegexp.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array( 'article/title' ),
                                    'regex'      => '(a|b|c)',
                                    'sort'       => FacetBuilder\FieldFacetBuilder::TERM_DESC
                                )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetFieldRegexpSortTerm.php',
            ),
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldFacetBuilder(
                                array(
                                    'fieldPaths' => array( 'article/title' ),
                                    'regex'      => '(a|b|c)',
                                    'sort'       => FacetBuilder\FieldFacetBuilder::COUNT_DESC
                                )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetFieldRegexpSortCount.php',
            ),
            /* @todo: Add sane ranges here:
            array(
                new Query(
                    array(
                        'filter'      => new Criterion\SectionId( array( 1 ) ),
                        'offset'      => 0,
                        'limit'       => 10,
                        'facetBuilders' => array(
                            new FacetBuilder\FieldRangeFacetBuilder( array(
                                'fieldPath' => 'product/price',
                            ) )
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . '/FacetFieldRegexpSortCount.php',
            ), // */
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getFacettedSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFindFacettedContent( Query $query, $fixture )
    {
        $this->assertQueryFixture( $query, $fixture );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testQueryCustomField()
    {
        $query = new Query(
            array(
                'query'       => new Criterion\CustomField(
                    'custom_field',
                    Criterion\Operator::EQ,
                    'AdMiNiStRaToR'
                ),
                'offset'      => 0,
                'limit'       => 10,
                'sortClauses' => array( new SortClause\ContentId() )
            )
        );
        $this->assertQueryFixture(
            $query,
            $this->getFixtureDir() . '/QueryCustomField.php'
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testQueryModifiedField()
    {
        $query = new Query(
            array(
                'query'       => new Criterion\Field(
                    'first_name',
                    Criterion\Operator::EQ,
                    'AdMiNiStRaToR'
                ),
                'offset'      => 0,
                'limit'       => 10,
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

        try
        {
            $result = $searchService->findContent( $query );
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
