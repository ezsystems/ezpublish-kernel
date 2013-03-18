<?php
/**
 * File containing the SearchServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\Core\Repository\SearchService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Test case for operations in the SearchService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class SearchServiceTest extends BaseTest
{
    public function getSearches()
    {
        $fixtureDir = $this->getFixtureDir();
        return array(
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\ContentId(
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
                        'criterion' => new Criterion\LogicalAnd(
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
                        'criterion' => new Criterion\LogicalOr(
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
                        'criterion' => new Criterion\LogicalAnd(
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
                        'criterion' => new Criterion\Subtree(
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
                        'criterion' => new Criterion\ContentTypeId(
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
                        'criterion' => new Criterion\ContentTypeGroupId(
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
                        'criterion' => new Criterion\DateMetadata(
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
                        'criterion' => new Criterion\DateMetadata(
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
                        'criterion' => new Criterion\DateMetadata(
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
                        'criterion' => new Criterion\DateMetadata(
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
                        'criterion' => new Criterion\DateMetadata(
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
                        'criterion' => new Criterion\DateMetadata(
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
                        'criterion' => new Criterion\LocationId(
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
                        'criterion' => new Criterion\ParentLocationId(
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
                        'criterion' => new Criterion\RemoteId(
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
                        'criterion' => new Criterion\LocationRemoteId(
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
                        'criterion' => new Criterion\SectionId(
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
                        'criterion' => new Criterion\Status(
                            array( Criterion\Status::STATUS_PUBLISHED )
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
                        'criterion' => new Criterion\Field(
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
                        'criterion' => new Criterion\Field(
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
                        'criterion' => new Criterion\DateMetadata(
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
                        'criterion' => new Criterion\LogicalOr(
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
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\FullText(
                            'contact'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FullText.php',
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\FullText(
                            'Contact*'
                        ),
                        'sortClauses' => array( new SortClause\ContentId() )
                    )
                ),
                $fixtureDir . 'FullTextWildcard.php',
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFindContent( Query $query, $fixture, $closure = null )
    {
        $this->assertQueryFixture( $query, $fixture, $closure );
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
     * @expectedException \OutOfBoundsException
     */
    public function testInvalidFieldIdentifierRange()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'criterion' => new Criterion\Field(
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
     * @expectedException \OutOfBoundsException
     */
    public function testInvalidFieldIdentifierIn()
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $searchService->findContent(
            new Query(
                array(
                    'criterion' => new Criterion\Field(
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

    public function getSortedSearches()
    {
        $fixtureDir = $this->getFixtureDir();
        return array(
            array(
                new Query(
                    array(
                        'criterion'   => new Criterion\SectionId( array( 2 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 2 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 2 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 3 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 2 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 2 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 4, 2, 6, 3 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 4, 2, 6, 3 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 2, 3 ) ),
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
                        'criterion' => new Criterion\ContentTypeId( 1 ),
                        'offset'      => 0,
                        'limit'       => null,
                        'sortClauses' => array(
                            new SortClause\Field( "folder", "name" ),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortFolderName.php',
            ),
            array(
                new Query(
                    array(
                        'criterion'   => new Criterion\SectionId( array( 5 ) ),
                        'offset'      => 0,
                        'limit'       => null,
                        'sortClauses' => array(
                            new SortClause\Field( "template_look", "title" ),
                            new SortClause\ContentId(),
                        )
                    )
                ),
                $fixtureDir . 'SortTemplateTitle.php',
            ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
                        'criterion'   => new Criterion\SectionId( array( 1 ) ),
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
     * Assert that query result matches the given fixture.
     *
     * @param Query $query
     * @param string $fixture
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
