<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\UrlAlias;

/**
 * Test case for Persistence\Cache\UrlAliasHandler
 */
class UrlAliasHandlerTest extends HandlerTest
{
    /**
     * @return array
     */
    function providerForUnCachedMethods()
    {
        return array(
            //array( 'publishUrlAliasForLocation', array( 44, 2, 'name', 'eng-GB', true ) ),
            //array( 'createCustomUrlAlias', array( 44, '/path', true, 'eng-GB', true ) ),
            //array( 'createGlobalUrlAlias', array( '/old', '/path', true, 'eng-GB', true ) ),
            array( 'listGlobalURLAliases', array( 'eng-GB', 10, 5 ) ),
            //array( 'listURLAliasesForLocation', array( 44, true ) ),
            //array( 'removeURLAliases', array( array( 1, 2 ) ) ),
            //array( 'lookup', array( '/url' ) ),
            //array( 'loadUrlAlias', array( 88 ) ),
            //array( 'locationMoved', array( 44, 2, 45 ) ),
            array( 'locationCopied', array( 44, 2, 45 ) ),
            //array( 'locationDeleted', array( 44 ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler
     */
    public function testUnCachedMethods( $method, array $arguments )
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $expects = $innerHandler
            ->expects( $this->once() )
            ->method( $method );

        if ( isset( $arguments[4] ) )
            $expects->with( $arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4] );
        else if ( isset( $arguments[3] ) )
            $expects->with( $arguments[0], $arguments[1], $arguments[2], $arguments[3] );
        else if ( isset( $arguments[2] ) )
            $expects->with( $arguments[0], $arguments[1], $arguments[2] );
        else if ( isset( $arguments[1] ) )
            $expects->with( $arguments[0], $arguments[1] );
        else if ( isset( $arguments[0] ) )
            $expects->with( $arguments[0] );

        $expects->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        call_user_func_array( array( $handler, $method ), $arguments );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasForLocation()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'publishUrlAliasForLocation' )
            ->with( 44, 2, 'name', 'eng-GB', true )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'urlAlias', 'location', 44 )
            ->will( $this->returnValue( null  ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->publishUrlAliasForLocation( 44, 2, 'name', 'eng-GB', true );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::createCustomUrlAlias
     */
    public function testCreateCustomUrlAlias()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'createCustomUrlAlias' )
            ->with( 44, '/path', true, 'eng-GB', true )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55, 'destination' => 44 ) ) ) );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'urlAlias', 'location', 44, 'custom' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->createCustomUrlAlias( 44, '/path', true, 'eng-GB', true );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::createGlobalUrlAlias
     */
    public function testCreateGlobalUrlAlias()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'createGlobalUrlAlias' )
            ->with( '/old', '/path', true, 'eng-GB', true )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'urlAlias', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->createGlobalUrlAlias( '/old', '/path', true, 'eng-GB', true );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'urlAlias', 'location', 44 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'listURLAliasesForLocation' )
            ->with( 44, false )
            ->will(
                $this->returnValue(
                    array(
                        new UrlAlias( array( 'id' => 55 ) ),
                        new UrlAlias( array( 'id' => 58 ) ),
                        new UrlAlias( array( 'id' => 91 ) )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( array( 55, 58, 91 ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation( 44, false );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationCustomIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'urlAlias', 'location', '44/custom' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'listURLAliasesForLocation' )
            ->with( 44, true )
            ->will(
                $this->returnValue(
                    array(
                        new UrlAlias( array( 'id' => 55 ) ),
                        new UrlAlias( array( 'id' => 58 ) ),
                        new UrlAlias( array( 'id' => 91 ) )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( array( 55, 58, 91 ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation( 44, true );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 'location', 44 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( array( 55, 58, 91 ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        // inline calls to loadUrlAlias() using the cache
        $cacheItemMock2 = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 55 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->at( 0 ) )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $this->cacheMock
            ->expects( $this->at( 2 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 58 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->at( 1 ) )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 58 ) ) ) );

        $this->cacheMock
            ->expects( $this->at( 3 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 91 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->at( 2 ) )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 91 ) ) ) );

        $cacheItemMock2
            ->expects( $this->exactly( 3 ) )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock2
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation( 44, false );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationCustomHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 'location', '44/custom' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( array( 55, 58, 91 ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

                // inline calls to loadUrlAlias() using the cache
        $cacheItemMock2 = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 55 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->at( 0 ) )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $this->cacheMock
            ->expects( $this->at( 2 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 58 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->at( 1 ) )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 58 ) ) ) );

        $this->cacheMock
            ->expects( $this->at( 3 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 91 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->at( 2 ) )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 91 ) ) ) );

        $cacheItemMock2
            ->expects( $this->exactly( 3 ) )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock2
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation( 44, true );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::removeURLAliases
     */
    public function testRemoveURLAliases()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'removeURLAliases' );

        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'clear' )
            ->with( 'urlAlias', 'url' );

        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'clear' )
            ->with( 'urlAlias', 21 );

        $this->cacheMock
            ->expects( $this->at( 2 ) )
            ->method( 'clear' )
            ->with( 'urlAlias', 32 );

        $this->cacheMock
            ->expects( $this->at( 3 ) )
            ->method( 'clear' )
            ->with( 'urlAlias', 'location', 44 );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->removeURLAliases(
            array(
                new UrlAlias( array( 'id' => 21 ) ),
                new UrlAlias( array( 'id' => 32, 'type' => UrlAlias::LOCATION, 'destination' => 44 ) ),
            )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::lookup
     */
    public function testLookupIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'urlAlias', 'url', '/url' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'lookup' )
            ->with( '/url' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( 55 );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->lookup( '/url' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::lookup
     */
    public function testLookupHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( 'logCall' );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 0 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 'url', '/url' )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( 55 ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $cacheItemMock2 = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->at( 1 ) )
            ->method( 'getItem' )
            ->with( 'urlAlias', 55 )
            ->will( $this->returnValue( $cacheItemMock2 ) );

        $cacheItemMock2
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $cacheItemMock2
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock2
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->lookup( '/url' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::loadUrlAlias
     */
    public function testLoadUrlAliasIsMiss()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'urlAlias', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( null ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadUrlAlias' )
            ->with( 55 )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias' ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->loadUrlAlias( 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::loadUrlAlias
     */
    public function testLoadUrlAliasHasCache()
    {
        $this->loggerMock->expects( $this->never() )->method( 'logCall' );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $cacheItemMock = $this->getMock( 'Stash\\Item', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->with( 'urlAlias', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( new UrlAlias( array( 'id' => 55 ) ) ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->loadUrlAlias( 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::locationMoved
     */
    public function testLocationMoved()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'locationMoved' )
            ->with( 44, 2, 45 );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'urlAlias' )
            ->will( $this->returnValue( null  ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->locationMoved( 44, 2, 45 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::locationDeleted
     */
    public function testLocationDeleted()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logCall' );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getUrlAliasHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'locationDeleted' )
            ->with( 44 );

        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'urlAlias', 'location', 44 )
            ->will( $this->returnValue( null  ) );

        $handler = $this->persistenceHandler->urlAliasHandler();
        $handler->locationDeleted( 44 );
    }
}
