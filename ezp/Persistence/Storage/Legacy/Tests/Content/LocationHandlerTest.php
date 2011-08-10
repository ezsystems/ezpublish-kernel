<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\LocationHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content,
    ezp\Persistence\Storage\Legacy\Content\Location\Handler,
    ezp\Persistence,
    ezp\Persistence\Content\Location\CreateStruct;

/**
 * Test case for LocationHandlerTest
 */
class LocationHandlerTest extends TestCase
{
    /**
     * Mocked content handler instance
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Mocked location gateway instance
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

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
            $this->locationGateway = $this->getMock( 'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Gateway' )
        );
    }

    public static function getLoadLocationValues()
    {
        return array(
            array( 'id', 77 ),
            array( 'priority', 0 ),
            array( 'hidden', 0 ),
            array( 'invisible', 0 ),
            array( 'remoteId', 'dbc2f3c8716c12f32c379dbf0b1cb133' ),
            array( 'contentId', 75 ),
            array( 'parentId', 2 ),
            array( 'pathIdentificationString', 'solutions' ),
            array( 'pathString', '/1/2/77/' ),
            array( 'modifiedSubLocation', 1311065017 ),
            array( 'mainLocationId', 77 ),
            array( 'depth', 2 ),
            array( 'sortField', 2 ),
            array( 'sortOrder', 1 ),
        );
    }

    /**
     * @dataProvider getLoadLocationValues
     */
    public function testLoadLocation( $field, $value )
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
                        'priority' => 0,
                        'is_hidden' => 0,
                        'is_invisible' => 0,
                        'remote_id' => 'dbc2f3c8716c12f32c379dbf0b1cb133',
                        'contentobject_id' => 75,
                        'parent_node_id' => 2,
                        'path_identification_string' => 'solutions',
                        'path_string' => '/1/2/77/',
                        'modified_subnode' => 1311065017,
                        'main_node_id' => 77,
                        'depth' => 2,
                        'sort_field' => 2,
                        'sort_order' => 1,
                    )
                )
            );

        $location = $handler->load( 77 );

        $this->assertTrue( $location instanceof \ezp\Persistence\Content\Location );
        $this->assertEquals(
            $value,
            $location->$field,
            "Value in property $field not as expected."
        );
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
            ->method( 'updateNodeAssignement' )
            ->with( 67, 77 );

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
            ->method( 'createLocation' )
            ->with( $createStruct, $parentInfo );

        $handler->createLocation( $createStruct, 77 );
    }

    public function testTrashSubtree()
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
            ->method( 'trashSubtree' )
            ->with( '/1/2/69/' );

        $handler->trashSubtree( 69 );
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
}
