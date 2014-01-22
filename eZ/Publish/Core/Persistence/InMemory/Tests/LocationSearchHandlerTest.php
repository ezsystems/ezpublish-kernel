<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\LocationSearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Test case for Location Search Handler using in memory storage.
 */
class LocationSearchHandlerTest extends LocationHandlerTest
{
    /**
     * Test for findLocations() method.
     *
     * @dataProvider providerForTestFindLocations
     * @covers \eZ\Publish\Core\Persistence\InMemory\LocationSearchHandler::findLocations
     * @group locationSearchHandler
     */
    public function testFindLocations( Query $query, $results )
    {
        $locations = $this->persistenceHandler->locationSearchHandler()->findLocations( $query );
        usort(
            $locations,
            function ( $a, $b )
            {
                if ( $a->id == $b->id )
                    return 0;

                return ( $a->id < $b->id ) ? -1 : 1;
            }
        );
        $this->assertEquals( count( $results ), count( $locations ) );
        foreach ( $results as $n => $result )
        {
            foreach ( $result as $key => $value )
            {
                $this->assertEquals( $value, $locations[$n]->$key );
            }
        }
    }

    public function providerForTestFindLocations()
    {
        return array(
            array(
                new Query(
                    array(
                        "filter" => new Criterion\ParentLocationId( 1 )
                    )
                ),
                array(
                    array( "id" => 2, "parentId" => 1 ),
                    array( "id" => 5, "parentId" => 1 ),
                    array( "id" => 43, "parentId" => 1 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\ContentId( 54 )
                    )
                ),
                array( array( "id" => 56, "contentId" => 54 ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LocationRemoteId( "locationRemote1" )
                    )
                ),
                array( array( "id" => 55, "remoteId" => "locationRemote1" ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\SectionId( 3 )
                    )
                ),
                array(
                    array( "id" => 43 ),
                    array( "id" => 53 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\RemoteId( "contentRemote1" )
                    )
                ),
                array(
                    array( "id" => 55, "remoteId" => "locationRemote1" ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\ContentTypeId( 3 )
                    )
                ),
                array(
                    array( "id" => 5 ),
                    array( "id" => 12 ),
                    array( "id" => 13 ),
                    array( "id" => 44 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\ContentTypeIdentifier( "user_group" )
                    )
                ),
                array(
                    array( "id" => 5 ),
                    array( "id" => 12 ),
                    array( "id" => 13 ),
                    array( "id" => 44 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\ContentTypeGroupId( 2 )
                    )
                ),
                array(
                    array( "id" => 5 ),
                    array( "id" => 12 ),
                    array( "id" => 13 ),
                    array( "id" => 44 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\ParentLocationId( 54 )
                    )
                ),
                array( array( "id" => 55, "parentId" => 54 ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LocationId( 55 )
                    )
                ),
                array( array( "id" => 55 ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LocationRemoteId( "locationRemote1" ),
                                new Criterion\ParentLocationId( 54 )
                            )
                        )
                    )
                ),
                array( array( "id" => 55, "parentId" => 54, "remoteId" => "locationRemote1" ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LogicalAnd(
                                    array(
                                        new Criterion\LocationRemoteId( "locationRemote1" ),
                                        new Criterion\ParentLocationId( 54 )
                                    )
                                ),
                                new Criterion\ParentLocationId( 54 )
                            )
                        )
                    )
                ),
                array( array( "id" => 55, "parentId" => 54, "remoteId" => "locationRemote1" ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LocationId( 55 ),
                                new Criterion\LogicalAnd(
                                    array(
                                        new Criterion\LocationRemoteId( "locationRemote1" ),
                                        new Criterion\ParentLocationId( 54 )
                                    )
                                )
                            )
                        )
                    )
                ),
                array( array( "id" => 55, "parentId" => 54, "remoteId" => "locationRemote1" ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LocationId( 54 ),
                                new Criterion\LogicalAnd(
                                    array(
                                        new Criterion\LocationRemoteId( "locationRemote1" ),
                                        new Criterion\ParentLocationId( 54 )
                                    )
                                )
                            )
                        )
                    )
                ),
                array()
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LocationRemoteId( "locationRemote0" ),
                                new Criterion\ParentLocationId( 54 )
                            )
                        )
                    )
                ),
                array()
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\ParentLocationId( 1 ),
                                new Criterion\LocationId( 43 ),
                            )
                        )
                    )
                ),
                array(
                    array( "id" => 43, "parentId" => 1 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\ParentLocationId( 1 ),
                                new Criterion\ParentLocationId( 1 ),
                                new Criterion\ParentLocationId( 1 ),
                            )
                        )
                    )
                ),
                array(
                    array( "id" => 2, "parentId" => 1 ),
                    array( "id" => 5, "parentId" => 1 ),
                    array( "id" => 43, "parentId" => 1 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalOr(
                            array(
                                new Criterion\LocationRemoteId( "locationRemote1" ),
                                new Criterion\ParentLocationId( 54 ),
                                new Criterion\LocationRemoteId( "ARemoteIdThatDoesNotExist" ),
                            )
                        )
                    )
                ),
                array( array( "id" => 55, "parentId" => 54, "remoteId" => "locationRemote1" ) )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalOr(
                            array(
                                new Criterion\LocationRemoteId( "locationRemote0" ),
                                new Criterion\LogicalOr(
                                    array(
                                        new Criterion\LocationRemoteId( "locationRemote1" ),
                                        new Criterion\ParentLocationId( 54 ),
                                    )
                                )
                            )
                        )
                    )
                ),
                array(
                    array( "id" => 54, "remoteId" => "locationRemote0" ),
                    array( "id" => 55, "parentId" => 54, "remoteId" => "locationRemote1" )
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalOr(
                            array(
                                new Criterion\LocationRemoteId( "locationRemote1" ),
                                new Criterion\LocationRemoteId( "ARemoteIdThatDoesNotExist" ),
                            )
                        )
                    )
                ),
                array(
                    array( "id" => 55, "remoteId" => "locationRemote1" ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\Subtree(
                            "/1/2/"
                        )
                    )
                ),
                array(
                    array( "id" => 54 ),
                    array( "id" => 55 ),
                    array( "id" => 56 ),
                    array( "id" => 57 ),
                    array( "id" => 58 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Subtree(
                                    "/1/2/"
                                ),
                                new Criterion\LogicalNot(
                                    new Criterion\LocationRemoteId( "locationRemote1" )
                                ),
                            )
                        )
                    )
                ),
                array(
                    array( "id" => 54 ),
                    array( "id" => 56 ),
                    array( "id" => 57 ),
                    array( "id" => 58 ),
                )
            ),
            array(
                new Query(
                    array(
                        "filter" => new Criterion\LogicalAnd(
                            array(
                                new Criterion\LogicalNot(
                                    new Criterion\Subtree(
                                        "/1/2/"
                                    )
                                ),
                                new Criterion\LogicalNot(
                                    new Criterion\Subtree(
                                        "/1/5/"
                                    )
                                ),
                            )
                        )
                    )
                ),
                array(
                    array( "id" => 1, "parentId" => 0 ),
                    array( "id" => 2, "parentId" => 1 ),
                    array( "id" => 5, "parentId" => 1 ),
                    array( "id" => 43, "parentId" => 1 ),
                    array( "id" => 53, "parentId" => 43 ),
                )
            ),
        );
    }
}
