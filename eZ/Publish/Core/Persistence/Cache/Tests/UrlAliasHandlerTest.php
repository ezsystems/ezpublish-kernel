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
            array( 'publishUrlAliasForLocation', array( 44, 2, 'name', 'eng-GB', true ) ),
            array( 'createCustomUrlAlias', array( 44, '/path', true, 'eng-GB', true ) ),
            array( 'createGlobalUrlAlias', array( '/old', '/path', true, 'eng-GB', true ) ),
            array( 'listGlobalURLAliases', array( 'eng-GB', 10, 5 ) ),
            array( 'listURLAliasesForLocation', array( 44, true ) ),
            array( 'removeURLAliases', array( array( 1, 2 ) ) ),
            array( 'lookup', array( '/url' ) ),
            array( 'loadUrlAlias', array( 88 ) ),
            array( 'locationMoved', array( 44, 2, 45 ) ),
            array( 'locationCopied', array( 44, 2, 45 ) ),
            array( 'locationDeleted', array( 44 ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers eZ\Publish\Core\Persistence\Cache\ContentHandler
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
}
