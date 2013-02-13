<?php
/**
 * File containing the LocalPurgeClientTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient;

class LocalPurgeClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient::purge
     */
    public function testPurge()
    {
        $cacheStore = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Cache\\Http\\ContentPurger' );
        $cacheStore
            ->expects( $this->once() )
            ->method( 'purgeByRequest' )
            ->with( $this->isInstanceOf( 'Symfony\\Component\\HttpFoundation\\Request' ) );

        $purgeClient = new LocalPurgeClient( $cacheStore );
        $purgeClient->purge( array( 123, 456, 789 ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Cache\Http\LocalPurgeClient::purgeAll
     */
    public function testPurgeAll()
    {
        $cacheStore = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Cache\\Http\\ContentPurger' );
        $cacheStore
            ->expects( $this->once() )
            ->method( 'purgeAllContent' );

        $purgeClient = new LocalPurgeClient( $cacheStore );
        $purgeClient->purgeAll();
    }
}
