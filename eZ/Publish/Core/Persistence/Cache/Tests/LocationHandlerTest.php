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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

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

        $handler = $this->persistenceHandler->locationHandler();
        $handler->load( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::load
     */
    public function testLoadHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( new Location(  array( 'id' => 33  ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( 'getLocationHandler' );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->load( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::loadLocationsByContent
     */
    public function testLoadLocationsByContentIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'content', 'locations', 44 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

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
            ->method(  'loadLocationsByContent' )
            ->with( 44 )
            ->will( $this->returnValue( array( new Location(  array( 'id' => 33  ) ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( array( 33 ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->loadLocationsByContent( 44 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::loadLocationsByContent
     */
    public function testLoadLocationsByContentHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'getItem' )
            ->with( 'content', 'locations', 44 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( array( 33 ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        // inline call to load()
        $cacheItemMock2 = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'getItem' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( new Location(  array( 'id' => 33 ) ) ) );

        $cacheItemMock2
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock2
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->loadLocationsByContent( 44 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::loadLocationsByContent
     */
    public function testLoadLocationsByContentWithRootIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'content', 'locations', '44/root/2' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

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
            ->method(  'loadLocationsByContent' )
            ->with( 44, 2 )
            ->will( $this->returnValue( array( new Location(  array( 'id' => 33  ) ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( array( 33 ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->loadLocationsByContent( 44, 2 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::loadLocationsByContent
     */
    public function testLoadLocationsByContentWithRootHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'getItem' )
            ->with( 'content', 'locations', '44/root/2' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( array( 33 ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        // inline call to load()
        $cacheItemMock2 = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'getItem' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( new Location(  array( 'id' => 33 ) ) ) );

        $cacheItemMock2
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock2
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->loadLocationsByContent( 44, 2 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'location', 33 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'location', 66 )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 2 ) )
            ->method( 'clear' )
            ->with( 'content', 'locations' )
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
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
            ->will( $this->returnValue( new Location(  array( 'id' => 33, 'contentId' => 2  ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'content', 'locations', 2 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->locationHandler();
        $handler->create( new CreateStruct );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\LocationHandler::removeSubtree
     */
    public function testRemoveSubtree()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'location' )
            ->will( $this->returnValue( true ) );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'content' )
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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'content' );

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
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'content', 'info', 30 )
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
