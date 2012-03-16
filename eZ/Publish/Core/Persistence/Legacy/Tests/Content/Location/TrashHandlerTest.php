<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Trash\TrashHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash\Handler,
    eZ\Publish\SPI\Persistence,
    eZ\Publish\SPI\Persistence\Content\Location\Trashed,
    eZ\Publish\SPI\Persistence\Content\Location\Trash\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\Trash\CreateStruct;

/**
 * Test case for TrashHandlerTest
 */
class TrashHandlerTest extends TestCase
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

    protected function getTrashHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        return new Handler(
            $this->locationGateway = $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway' ),
            $this->locationMapper = $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper' )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash\Handler::trash
     */
    public function testTrash()
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

        $this->locationGateway
            ->expects( $this->once() )
            ->method( 'loadTrashByLocation' )
            ->with( 69 )
            ->will( $this->returnValue( $array = array( 'data…' ) ) );

        $this->locationMapper
            ->expects( $this->once() )
            ->method( 'createLocationFromRow' )
            ->with( $array, null, new Trashed() )
            ->will( $this->returnValue( new Trashed( array( 'id' => 69 ) ) ) );

        $trashedObject = $handler->trash( 69 );
        self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trashed', $trashedObject );
        self::assertSame( 69, $trashedObject->id );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash::recover
     */
    public function testRecover()
    {
        $handler = $this->getTrashHandler();

        $this->locationGateway
            ->expects( $this->at( 0 ) )
            ->method( 'untrashLocation' )
            ->with( 69, 23 )
            ->will(
                $this->returnValue(
                    new Trashed( array( 'id' => 70 ) )
                )
            );

        self::assertSame( 70, $handler->recover( 69, 23 ) );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash::loadTrashItem
     */
    public function testLoadTrashItem()
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

        $handler->loadTrashItem( 69 );
    }
}
