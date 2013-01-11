<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;

/**
 * Test case for Persistence\Cache\LocationHandler
 */
class LocationHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::load
     */
    public function testLoadCacheIsMiss()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'load' )
            ->with( 33 )
            ->will( $this->returnValue( new Location(  array( 'id' => 33  ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location' ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->load( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::load
     */
    public function testLoadHasCache()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( 'getLocationHandler' );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( new Location(  array( 'id' => 33  ) ) ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->load( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::loadLocationsByContent
     */
    public function testLoadLocationsByContent()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadLocationsByContent' )
            ->with( 33, 44 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->loadLocationsByContent( 33, 44 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadByRemoteId' )
            ->with( 'sve45gdy4e' )
            ->will( $this->returnValue( new Location(  array( 'id' => 33  ) ) ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->loadByRemoteId( 'sve45gdy4e' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::copySubtree
     */
    public function testCopySubtree()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'copySubtree' )
            ->with( 55, 66 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->copySubtree( 55, 66 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::move
     */
    public function testMove()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'location' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'move' )
            ->with( 33, 66 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->move( 33, 66 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::markSubtreeModified
     */
    public function testMarkSubtreeModified()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'markSubtreeModified' )
            ->with( 55 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->markSubtreeModified( 55 );
    }
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::hide
     */
    public function testHide()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'location' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'hide' )
            ->with( 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->hide( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::unhide
     */
    public function testUnhide()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'location' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'unhide' )
            ->with( 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->unhide( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::swap
     */
    public function testSwap()
    {
        $this->cacheMock
            ->expects( $this->exactly( 2 ) )
            ->method( 'clear' )
            ->with( 'location', $this->greaterThanOrEqual( 33 ) )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'swap' )
            ->with( 33, 66 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->swap( 33, 66 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::update
     */
    public function testUpdate()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'update' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\UpdateStruct' ), 33 )
            ->will( $this->returnValue( new Location(  array( 'id' => 33  ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->update( new UpdateStruct, 33  );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::create
     */
    public function testCreate()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'create' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\CreateStruct' ) )
            ->will( $this->returnValue( new Location(  array( 'id' => 33  ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->create( new CreateStruct );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::removeSubtree
     */
    public function testRemoveSubtree()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'location' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'removeSubtree' )
            ->with( 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->removeSubtree( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::setSectionForSubtree
     */
    public function testSetSectionForSubtree()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'setSectionForSubtree' )
            ->with( 33, 2 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->setSectionForSubtree( 33, 2 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::changeMainLocation
     */
    public function testChangeMainLocation()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'location' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getLocationHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'changeMainLocation' )
            ->with( 30, 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->changeMainLocation( 30, 33 );
    }
}
