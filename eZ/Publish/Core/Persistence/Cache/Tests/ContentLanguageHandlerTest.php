<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Language as SPILanguage;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct as SPILanguageCreateStruct;

/**
 * Test case for Persistence\Cache\ContentLanguageHandler
 */
class ContentLanguageHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler::create
     */
    public function testCreate()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'language', 2 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentLanguageHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'create' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\CreateStruct' ) )
            ->will(
                $this->returnValue(
                    new SPILanguage(
                        array( 'id' => 2, 'name' => 'English (UK)', 'languageCode' => 'eng-GB'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->contentLanguageHandler();
        $handler->create( new SPILanguageCreateStruct );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler::delete
     */
    public function testDelete()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'language', 2 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentLanguageHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'delete' )
            ->with( 2 )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentLanguageHandler();
        $handler->delete( 2 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler::load
     */
    public function testLoadCacheIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'language', 2 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentLanguageHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'load' )
            ->with( 2 )
            ->will(
                $this->returnValue(
                    new SPILanguage(
                        array( 'id' => 2, 'name' => 'English (UK)', 'languageCode' => 'eng-GB'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language' ) );

        $handler = $this->persistenceHandler->contentLanguageHandler();
        $handler->load( 2 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler::load
     */
    public function testLoadHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'language', 2 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will(
                $this->returnValue(
                    new SPILanguage(
                        array( 'id' => 2, 'name' => 'English (UK)', 'languageCode' => 'eng-GB'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( 'getContentLanguageHandler' );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->contentLanguageHandler();
        $handler->load( 2 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler::loadAll
     */
    public function testLoadAll()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentLanguageHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadAll' )
            ->will( $this->returnValue( array() ) );

        $handler = $this->persistenceHandler->contentLanguageHandler();
        $handler->loadAll();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler::loadByLanguageCode
     */
    public function testLoadByLanguageCode()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentLanguageHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadByLanguageCode' )
            ->with( 'eng-GB' )
            ->will(
                $this->returnValue(
                    new SPILanguage(
                        array( 'id' => 2, 'name' => 'English (UK)', 'languageCode' => 'eng-GB'  )
                    )
                )
            );

        $handler = $this->persistenceHandler->contentLanguageHandler();
        $handler->loadByLanguageCode( 'eng-GB' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler::update
     */
    public function testUpdate()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'language', 2 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentLanguageHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'update' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language' ) )
            ->will(
                $this->returnValue(
                    new SPILanguage(
                        array( 'id' => 2, 'name' => 'English (UK)', 'languageCode' => 'eng-GB'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->contentLanguageHandler();
        $handler->update( new SPILanguage( array( 'id' => 2 ) ) );
    }
}
