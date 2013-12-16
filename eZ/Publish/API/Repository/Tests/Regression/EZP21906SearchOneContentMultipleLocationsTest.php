<?php
/**
 * File containing the EZP21906SearchOneContentMultipleLocationsTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * @issue EZP-21906
 */
class EZP21906SearchOneContentMultipleLocationsTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();

        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();

        // Adding locations for content #58 ("Contact Us").
        // We first need to create "containers" since only one location of a content can exist at a time under the same parent.
        $contentCreateStruct1 = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier( 'folder' ),
            'eng-GB'
        );
        $contentCreateStruct1->setField( 'name', 'EZP-21906-1' );
        $draft1 = $contentService->createContent(
            $contentCreateStruct1,
            array( $locationService->newLocationCreateStruct( 2 ) )
        );
        $folder1 = $contentService->publishVersion( $draft1->versionInfo );
        $locationsFolder1 = $locationService->loadLocations( $folder1->contentInfo );

        $contentCreateStruct2 = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier( 'folder' ),
            'eng-GB'
        );
        $contentCreateStruct2->setField( 'name', 'EZP-21906-2' );
        $draft2 = $contentService->createContent(
            $contentCreateStruct2,
            array( $locationService->newLocationCreateStruct( 2 ) )
        );
        $folder2 = $contentService->publishVersion( $draft2->versionInfo );
        $locationsFolder2 = $locationService->loadLocations( $folder2->contentInfo );

        $feedbackFormContentInfo = $contentService->loadContentInfo( 58 );
        $locationCreateStruct1 = $locationService->newLocationCreateStruct( $locationsFolder1[0]->id );
        $locationService->createLocation( $feedbackFormContentInfo, $locationCreateStruct1 );
        $locationCreateStruct2 = $locationService->newLocationCreateStruct( $locationsFolder2[0]->id );
        $locationService->createLocation( $feedbackFormContentInfo, $locationCreateStruct2 );
    }

    /**
     * @dataProvider searchContentQueryProvider
     */
    public function testSearchContentMultipleLocations( Query $query, $expectedResultCount )
    {
        $result = $this->getRepository()->getSearchService()->findContent( $query );
        $this->assertSame( $expectedResultCount, $result->totalCount );
        $this->assertSame( $expectedResultCount, count( $result->searchHits ) );
    }

    public function searchContentQueryProvider()
    {
        return array(
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'feedback_form' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                    )
                ),
                1
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'feedback_form' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName() )
                    )
                ),
                1
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'feedback_form' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName(), new SortClause\LocationPriority() )
                    )
                ),
                1
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'feedback_form' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName( Query::SORT_DESC ) )
                    )
                ),
                1
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'feedback_form' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName( Query::SORT_DESC ), new SortClause\LocationPriority(), new SortClause\DatePublished() )
                    )
                ),
                1
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'folder' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName() )
                    )
                ),
                2
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'folder' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName( Query::SORT_DESC ) )
                    )
                ),
                2
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'folder' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName( Query::SORT_DESC ), new SortClause\LocationPriority(), new SortClause\DatePublished() )
                    )
                ),
                2
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'folder' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName( Query::SORT_DESC ), new SortClause\LocationPriority(), new SortClause\DatePublished(), new SortClause\ContentId() )
                    )
                ),
                2
            ),
            array(
                new Query(
                    array(
                        'criterion' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree( '/1/2' ),
                                new Criterion\ContentTypeIdentifier( 'product' ),
                                new Criterion\Visibility( Criterion\Visibility::VISIBLE )
                            )
                        ),
                        'sortClauses' => array( new SortClause\ContentName(), new SortClause\LocationPriority() )
                    )
                ),
                0
            ),
        );
    }
}
