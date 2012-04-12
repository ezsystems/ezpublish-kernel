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
    eZ\Publish\Core\Persistence\Legacy\Content,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler,
    eZ\Publish\SPI\Persistence,
    eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\ContentInfo;

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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler::changeMainLocation()
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
}
