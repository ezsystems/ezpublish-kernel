<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Section as SPISection;

/**
 * Test case for Persistence\Cache\SectionHandler
 */
class SectionHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::assign
     */
    public function testAssign()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'content', 'info', 44 )
            ->will( $this->returnValue( null ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'assign' )
            ->with( 33, 44 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->assign( 33, 44 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::assignmentsCount
     */
    public function testAssignmentsCount()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'assignmentsCount' )
            ->with( 33 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->assignmentsCount( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::create
     */
    public function testCreate()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'create' )
            ->with( 'Intranet', 'intranet' )
            ->will(
                $this->returnValue(
                    new SPISection(
                        array( 'id' => 33, 'name' => 'Intranet', 'identifier' => 'intranet'  )
                    )
                )
            );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'section', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->create( 'Intranet', 'intranet' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::delete
     */
    public function testDelete()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'delete' )
            ->with( 33 )
            ->will(
                $this->returnValue( true )
            );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'section', 33 )
            ->will( $this->returnValue( true ) );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->delete( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::load
     */
    public function testLoadCacheIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'section', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'load' )
            ->with( 33 )
            ->will(
                $this->returnValue(
                    new SPISection(
                        array( 'id' => 33, 'name' => 'Intranet', 'identifier' => 'intranet'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section' ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->load( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::load
     */
    public function testLoadHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'section', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( 'getSectionHandler' );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will(
                $this->returnValue(
                    new SPISection(
                        array( 'id' => 33, 'name' => 'Intranet', 'identifier' => 'intranet'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->load( 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::loadAll
     */
    public function testLoadAll()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadAll' )
            ->will( $this->returnValue( array() ) );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->loadAll();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::loadByIdentifier
     */
    public function testLoadByIdentifier()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadByIdentifier' )
            ->with( 'intranet' )
            ->will(
                $this->returnValue(
                    new SPISection(
                        array( 'id' => 33, 'name' => 'Intranet', 'identifier' => 'intranet'  )
                    )
                )
            );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->loadByIdentifier( 'intranet' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\SectionHandler::update
     */
    public function testUpdate()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getSectionHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'update' )
            ->with( 33, 'Old Intranet', 'old_intranet' )
            ->will(
                $this->returnValue(
                    new SPISection(
                        array( 'id' => 33, 'name' => 'Old Intranet', 'identifier' => 'old_intranet'  )
                    )
                )
            );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'section', 33 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->sectionHandler();
        $handler->update( 33, 'Old Intranet', 'old_intranet' );
    }
}
