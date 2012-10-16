<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\LocationHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler,
    eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper;

/**
 * Test case for LocationHandlerTest
 */
class LocationHandlerTest extends TestCase
{
    /**
     * Mocked location gateway instance
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Mocked location mapper instance
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Mocked content handler instance
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    protected function getLocationHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new Handler(
            $this->locationGateway = $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway' ),
            $this->locationMapper = $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper' ),
            $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Handler', array(), array(), '', false ),
            $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper', array(), array(), '', false )
        );
    }

    public function testLoadLocation()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'getBasicNodeData' )
            ->with( 77 )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 77,
                    )
                )
            );

        $this->locationMapper
            ->expects( $this->once() )
            ->method( 'createLocationFromRow' )
            ->with( array( 'node_id' => 77 ) )
            ->will( $this->returnValue( new \eZ\Publish\SPI\Persistence\Content\Location() ) );

        $location = $handler->load( 77 );

        $this->assertTrue( $location instanceof \eZ\Publish\SPI\Persistence\Content\Location );
    }

    public function testLoadLocationByRemoteId()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'getBasicNodeDataByRemoteId' )
            ->with( 'abc123' )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 77,
                    )
                )
            );

        $this->locationMapper
            ->expects( $this->once() )
            ->method( 'createLocationFromRow' )
            ->with( array( 'node_id' => 77 ) )
            ->will( $this->returnValue( new \eZ\Publish\SPI\Persistence\Content\Location() ) );

        $location = $handler->loadByRemoteId( 'abc123' );

        $this->assertTrue( $location instanceof \eZ\Publish\SPI\Persistence\Content\Location );
    }

    public function testLoadLocationsByContent()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'loadLocationDataByContent' )
            ->with( 23, 42 )
            ->will(
                $this->returnValue(
                    array()
                )
            );

        $this->locationMapper
            ->expects( $this->once() )
            ->method( 'createLocationsFromRows' )
            ->with( array() )
            ->will( $this->returnValue( array( 'a', 'b' ) ) );

        $locations = $handler->loadLocationsByContent( 23, 42 );

        $this->assertInternalType( 'array', $locations );
    }

    public function testMoveSubtree()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'getBasicNodeData' )
            ->with( 69 )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 69,
                        'path_string' => '/1/2/69/',
                        'parent_node_id' => 2,
                        'contentobject_id' => 67,
                    )
                )
            );

        $this->locationGateway
            ->expects( $this->at( 1 ) )
            ->method( 'getBasicNodeData' )
            ->with( 77 )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 77,
                        'path_string' => '/1/2/77/',
                    )
                )
            );

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'moveSubtreeNodes' )
            ->with( '/1/2/69/', '/1/2/77/' );

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'updateNodeAssignment' )
            ->with( 67, 2, 77, 5 );

        $handler->move( 69, 77 );
    }

    public function testHideUpdateHidden()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'getBasicNodeData' )
            ->with( 69 )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 69,
                        'path_string' => '/1/2/69/',
                        'contentobject_id' => 67,
                    )
                )
            );

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'hideSubtree' )
            ->with( '/1/2/69/' );

        $handler->hide( 69 );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'getBasicNodeData' )
            ->with( 69 )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 69,
                        'path_string' => '/1/2/69/',
                        'contentobject_id' => 67,
                    )
                )
            );

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'unhideSubtree' )
            ->with( '/1/2/69/' );

        $handler->unhide( 69 );
    }

    public function testSwapLocations()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'swap' )
            ->with( 70, 78 );

        $handler->swap( 70, 78 );
    }

    public function testCreateLocation()
    {
        $handler = $this->getLocationHandler();

        $createStruct = new CreateStruct();
        $createStruct->parentId = 77;

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'getBasicNodeData' )
            ->with( 77 )
            ->will(
                $this->returnValue(
                    $parentInfo = array(
                        'node_id' => 77,
                        'path_string' => '/1/2/77/',
                    )
                )
            );

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'create' )
            ->with( $createStruct, $parentInfo )
            ->will( $this->returnValue( $createStruct ) );

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'createNodeAssignment' )
            ->with( $createStruct, 77, 2 );

        $handler->create( $createStruct );
    }

    public function testUpdateLocation()
    {
        $handler = $this->getLocationHandler();

        $updateStruct = new UpdateStruct();
        $updateStruct->priority = 77;

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'update' )
            ->with( $updateStruct, 23 );

        $handler->update( $updateStruct, 23 );
    }

    public function testSetSectionForSubtree()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'getBasicNodeData' )
            ->with( 69 )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 69,
                        'path_string' => '/1/2/69/',
                        'contentobject_id' => 67,
                    )
                )
            );

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'setSectionForSubtree' )
            ->with( '/1/2/69/', 3 );

        $handler->setSectionForSubtree( 69, 3 );
    }

    public function testMarkSubtreeModified()
    {
        $handler = $this->getLocationHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'getBasicNodeData' )
            ->with( 69 )
            ->will(
                $this->returnValue(
                    array(
                        'node_id' => 69,
                        'path_string' => '/1/2/69/',
                        'contentobject_id' => 67,
                    )
                )
            );

        $this->locationGateway
            ->expects( $this->at( 1 ) )
            ->method( 'updateSubtreeModificationTime' )
            ->with( '/1/2/69/' );

        $handler->markSubtreeModified( 69 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler::changeMainLocation
     */
    public function testChangeMainLocation()
    {
        $contentHandlerMock = $this->getMock( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Handler", array(), array(), "", false );
        $locationGatewayMock = $this->getMock( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway" );
        $handler = $this->getMock(
            "\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Handler",
            array( "load", "setSectionForSubtree" ),
            array(
                $locationGatewayMock,
                $this->getMock( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper" ),
                $contentHandlerMock,
                $this->getMock( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper", array(), array(), "", false )
            )
        );

        $handler
            ->expects( $this->at( 0 ) )
            ->method( "load" )
            ->with( 34 )
            ->will( $this->returnValue( new Location( array( "parentId" => 42 ) ) ) );

        $handler
            ->expects( $this->at( 1 ) )
            ->method( "load" )
            ->with( 42 )
            ->will( $this->returnValue( new Location( array( "contentId" => 84 ) ) ) );

        $contentHandlerMock
            ->expects( $this->at( 0 ) )
            ->method( "loadContentInfo" )
            ->with( "12" )
            ->will( $this->returnValue( new ContentInfo( array( "currentVersionNo" => 1 ) ) ) );

        $contentHandlerMock
            ->expects( $this->at( 1 ) )
            ->method( "loadContentInfo" )
            ->with( "84" )
            ->will( $this->returnValue( new ContentInfo( array( "sectionId" => 4 ) ) ) );

        $locationGatewayMock
            ->expects( $this->once() )
            ->method( "changeMainLocation" )
            ->with( 12, 34, 1, 42 );

        $handler
            ->expects( $this->once() )
            ->method( "setSectionForSubtree" )
            ->with( 34, 4 );

        $handler->changeMainLocation( 12, 34 );
    }

    /**
     * Test for the removeSubtree() method.
     *
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler::removeSubtree
     */
    public function testRemoveSubtree()
    {
        $handler = $this->getPartlyMockedHandler( array( "changeMainLocation" ) );

        // Original call
        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( "getBasicNodeData" )
            ->with( 42 )
            ->will(
            $this->returnValue(
                array(
                    "contentobject_id" => 100,
                    "main_node_id" => 200
                )
            )
        );
        $this->locationGateway
            ->expects( $this->at( 1 ) )
            ->method( "getChildren" )
            ->with( 42 )
            ->will(
            $this->returnValue(
                array(
                    array( "node_id" => 201 ),
                    array( "node_id" => 202 )
                )
            )
        );

        // First recursive call
        $this->locationGateway
            ->expects( $this->at( 2 ) )
            ->method( "getBasicNodeData" )
            ->with( 201 )
            ->will(
            $this->returnValue(
                array(
                    "contentobject_id" => 101,
                    "main_node_id" => 201
                )
            )
        );
        $this->locationGateway
            ->expects( $this->at( 3 ) )
            ->method( "getChildren" )
            ->with( 201 )
            ->will( $this->returnValue( array() ) );
        $this->locationGateway
            ->expects( $this->at( 4 ) )
            ->method( "countLocationsByContentId" )
            ->with( 101 )
            ->will( $this->returnValue( 1 ) );
        $this->contentHandler
            ->expects( $this->once() )
            ->method( "removeRawContent" )
            ->with( 101 );
        $this->locationGateway
            ->expects( $this->at( 5 ) )
            ->method( "removeLocation" )
            ->with( 201 );
        $this->locationGateway
            ->expects( $this->at( 6 ) )
            ->method( "deleteNodeAssignment" )
            ->with( 101 );

        // Second recursive call
        $this->locationGateway
            ->expects( $this->at( 7 ) )
            ->method( "getBasicNodeData" )
            ->with( 202 )
            ->will(
            $this->returnValue(
                array(
                    "contentobject_id" => 102,
                    "main_node_id" => 202
                )
            )
        );
        $this->locationGateway
            ->expects( $this->at( 8 ) )
            ->method( "getChildren" )
            ->with( 202 )
            ->will(
            $this->returnValue( array() ) );
        $this->locationGateway
            ->expects( $this->at( 9 ) )
            ->method( "countLocationsByContentId" )
            ->with( 102 )
            ->will(
            $this->returnValue( 2 ) );
        $this->locationGateway
            ->expects( $this->at( 10 ) )
            ->method( "getFallbackMainNodeData" )
            ->with( 102, 202 )
            ->will(
            $this->returnValue(
                array(
                    "node_id" => 203,
                    "contentobject_version" => 1,
                    "parent_node_id" => 204
                )
            )
        );
        $handler
            ->expects( $this->once() )
            ->method( "changeMainLocation" )
            ->with( 102, 203, 1, 204 );
        $this->locationGateway
            ->expects( $this->at( 11 ) )
            ->method( "removeLocation" )
            ->with( 202 );
        $this->locationGateway
            ->expects( $this->at( 12 ) )
            ->method( "deleteNodeAssignment" )
            ->with( 102 );

        // Continuation of the original call
        $this->locationGateway
            ->expects( $this->at( 13 ) )
            ->method( "removeLocation" )
            ->with( 42 );
        $this->locationGateway
            ->expects( $this->at( 14 ) )
            ->method( "deleteNodeAssignment" )
            ->with( 100 );

        // Start
        $handler->removeSubtree( 42 );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler::copySubtree
     */
    public function testCopySubtree()
    {
        $handler = $this->getPartlyMockedHandler(
            array(
                "load",
                "changeMainLocation",
                "setSectionForSubtree",
                "create"
            )
        );
        $subtreeContentRows = array(
            array( "node_id" => 10, "main_node_id" => 1, "parent_node_id" => 3, "contentobject_id" => 21, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 0, "priority"=> 0, "path_identification_string" => "test", "sort_field" => 2, "sort_order" => 1 ),
            array( "node_id" => 11, "main_node_id" => 11, "parent_node_id" => 10, "contentobject_id" => 211, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 0, "priority"=> 0, "path_identification_string" => "test", "sort_field" => 2, "sort_order" => 1 ),
            array( "node_id" => 12, "main_node_id" => 15, "parent_node_id" => 10, "contentobject_id" => 215, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 0, "priority"=> 0, "path_identification_string" => "test", "sort_field" => 2, "sort_order" => 1 ),
            array( "node_id" => 13, "main_node_id" => 2, "parent_node_id" => 10, "contentobject_id" => 22, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 0, "priority"=> 0, "path_identification_string" => "test", "sort_field" => 2, "sort_order" => 1 ),
            array( "node_id" => 14, "main_node_id" => 11, "parent_node_id" => 13, "contentobject_id" => 211, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 0, "priority"=> 0, "path_identification_string" => "test", "sort_field" => 2, "sort_order" => 1 ),
            array( "node_id" => 15, "main_node_id" => 15, "parent_node_id" => 13, "contentobject_id" => 215, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 0, "priority"=> 0, "path_identification_string" => "test", "sort_field" => 2, "sort_order" => 1 ),
            array( "node_id" => 16, "main_node_id" => 16, "parent_node_id" => 15, "contentobject_id" => 216, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 0, "priority"=> 0, "path_identification_string" => "test", "sort_field" => 2, "sort_order" => 1 ),
        );
        $destinationData = array( "node_id" => 5, "main_node_id" => 5, "parent_node_id" => 4, "contentobject_id" => 200, "contentobject_version" => 1, "is_hidden" => 0, "is_invisible" => 1 );
        $mainLocationsMap = array( true, true, true, true, 1011, 1012, true );
        $updateMainLocationsMap = array( 1215 => 1015 );
        $offset = 1000;

        $this->locationGateway
            ->expects( $this->once() )
            ->method( "getSubtreeContent" )
            ->with( $subtreeContentRows[0]["node_id"] )
            ->will( $this->returnValue( $subtreeContentRows ) );
        $this->locationGateway
            ->expects( $this->once() )
            ->method( "getBasicNodeData" )
            ->with( $destinationData["node_id"] )
            ->will( $this->returnValue( $destinationData ) );

        $contentIds = array_values( array_unique( array_map(
            function ( $row ) { return $row["contentobject_id"]; },
            $subtreeContentRows ) ) );
        foreach ( $contentIds as $index => $contentId )
        {
            $this->contentHandler
                ->expects( $this->at( $index * 2 ) )
                ->method( "copy" )
                ->with( $contentId, 1 )
                ->will(
                    $this->returnValue(
                        new Content(
                            array(
                                "versionInfo" => new VersionInfo(
                                    array(
                                        "contentInfo" => new ContentInfo(
                                            array(
                                                "id" => $contentId + $offset,
                                                "currentVersionNo" => 1
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                );

            $this->contentHandler
                ->expects( $this->at( $index * 2 + 1 ) )
                ->method( "publish" )
                ->with(
                    $contentId + $offset,
                    1,
                    $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\MetadataUpdateStruct" )
                )
                ->will(
                    $this->returnValue(
                        new Content(
                            array(
                                "versionInfo" => new VersionInfo(
                                    array(
                                        "contentInfo" => new ContentInfo(
                                            array(
                                                "id" => $contentId + $offset
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                );
        }

        foreach ( $subtreeContentRows as $index => $row )
        {
            $mapper = new Mapper();
            $createStruct = $mapper->getLocationCreateStruct( $row );
            $this->locationMapper
                ->expects( $this->at( $index ) )
                ->method( "getLocationCreateStruct" )
                ->with( $row )
                ->will( $this->returnValue( $createStruct ) );

            $createStruct = clone $createStruct;
            $createStruct->contentId = $createStruct->contentId + $offset;
            $createStruct->parentId = $index === 0 ? $destinationData["node_id"] : $createStruct->parentId + $offset;
            $createStruct->invisible = true;
            $createStruct->mainLocationId = $mainLocationsMap[$index];
            $handler
                ->expects( $this->at( $index ) )
                ->method( 'create' )
                ->with( $createStruct )
                ->will(
                    $this->returnValue(
                        new Location(
                            array(
                                "id" => $row["node_id"] + $offset,
                                "hidden" => false,
                                "invisible" => true,
                                "mainLocationId" => $mainLocationsMap[$index] === true ?
                                    $row["node_id"] + $offset :
                                    $mainLocationsMap[$index]
                            )
                        )
                    )
                );
        }

        foreach ( $updateMainLocationsMap as $contentId => $locationId )
        {
            $handler
                ->expects( $this->any() )
                ->method( "changeMainLocation" )
                ->with( $contentId, $locationId );
        }

        $handler
            ->expects( $this->once() )
            ->method( "load" )
            ->with( $destinationData["node_id"] )
            ->will( $this->returnValue( new Location( array( "contentId" => $destinationData["contentobject_id"] ) ) ) );

        $this->contentHandler
            ->expects( $this->once() )
            ->method( "loadContentInfo" )
            ->with( $destinationData["contentobject_id"] )
            ->will( $this->returnValue( new ContentInfo( array( "sectionId" => 12345 ) ) ) );

        $handler
            ->expects( $this->once() )
            ->method( "setSectionForSubtree" )
            ->with( $subtreeContentRows[0]["node_id"] + $offset, 12345 );

        $handler->copySubtree(
            $subtreeContentRows[0]["node_id"],
            $destinationData["node_id"]
        );
    }

    /**
     * Returns the handler to test with $methods mocked
     *
     * @param string[] $methods
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler
     */
    protected function getPartlyMockedHandler( array $methods )
    {
        return $this->getMock(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Handler',
            $methods,
            array(
                $this->locationGateway = $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway', array(), array(), '', false ),
                $this->locationMapper = $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper', array(), array(), '', false ),
                $this->contentHandler = $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Handler', array(), array(), '', false ),
                $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper', array(), array(), '', false )
            )
        );
    }
}
