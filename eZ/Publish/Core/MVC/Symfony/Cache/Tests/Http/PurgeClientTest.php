<?php
/**
 * File containing the PurgeClientTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\PurgeClient;

class PurgeClientTest extends HttpBasedPurgeClientTest
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\PurgeClient::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\PurgeClient::purge
     */
    public function testPurge()
    {
        $purgeServer = 'http://localhost/';
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'http_cache.purge_servers' )
            ->will( $this->returnValue( array( $purgeServer ) ) );

        $locations = array( 123, 456, 789 );

        $this->httpBrowser
            ->expects( $this->at( 0 ) )
            ->method( 'call' )
            ->with( $purgeServer, 'PURGE', array( 'X-Location-Id' => $locations[0] ) );
        $this->httpBrowser
            ->expects( $this->at( 1 ) )
            ->method( 'call' )
            ->with( $purgeServer, 'PURGE', array( 'X-Location-Id' => $locations[1] ) );
        $this->httpBrowser
            ->expects( $this->at( 2 ) )
            ->method( 'call' )
            ->with( $purgeServer, 'PURGE', array( 'X-Location-Id' => $locations[2] ) );
        $this->httpClient
            ->expects( $this->once() )
            ->method( 'flush' );

        $purgeClient = new PurgeClient( $this->configResolver, $this->httpBrowser );
        $purgeClient->purge( $locations );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\PurgeClient::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\PurgeClient::purge
     */
    public function testPurgeAll()
    {
        $purgeServer = 'http://localhost/';
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'http_cache.purge_servers' )
            ->will( $this->returnValue( array( $purgeServer ) ) );

        $this->httpBrowser
            ->expects( $this->once() )
            ->method( 'call' )
            ->with( $purgeServer, 'PURGE', array( 'X-Location-Id' => '*' ) );

        $purgeClient = new PurgeClient( $this->configResolver, $this->httpBrowser );
        $purgeClient->purgeAll();
    }
}
