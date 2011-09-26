<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Trash\TrashHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Location;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content,
    ezp\Persistence\Storage\Legacy\Content\Location\Trash\Handler,
    ezp\Persistence,
    ezp\Persistence\Content\Location\Trashed,
    ezp\Persistence\Content\Location\Trash\UpdateStruct,
    ezp\Persistence\Content\Location\Trash\CreateStruct;

/**
 * Test case for TrashHandlerTest
 */
class TrashHandlerTest extends TestCase
{
    /**
     * Mocked location gateway instance
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Mocked location mapper instance
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Mapper
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

    protected function getTrashHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new Handler(
            $this->locationGateway = $this->getMock( 'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Gateway' ),
            $this->locationMapper = $this->getMock( 'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Mapper' )
        );
    }

    public function testTrashSubtree()
    {
        $handler = $this->getTrashHandler();

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

    public function testUntrashTrash()
    {
        $handler = $this->getTrashHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'untrashLocation' )
            ->with( 69, 23 );

        $handler->untrashLocation( 69, 23 );
    }

    public function testLoad()
    {
        $handler = $this->getTrashHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'loadTrashByLocation' )
            ->with( 69 )
            ->will( $this->returnValue( $array = array( 'data…' ) ) );

        $this->locationMapper
            ->expects( $this->at( 0 ) )
            ->method( 'createLocationFromRow' )
            ->with( $array, null, new Trashed() );

        $handler->load( 69 );
    }
}
