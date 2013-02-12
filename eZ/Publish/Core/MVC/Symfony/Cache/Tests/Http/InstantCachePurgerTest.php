<?php
/**
 * File containing the InstantCachePurgerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger;

class InstantCachePurgerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $purgeClient;

    protected function setUp()
    {
        parent::setUp();
        $this->purgeClient = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Cache\\PurgeClientInterface' );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger::purge
     */
    public function testPurge()
    {
        $locationIds = array( 123, 456, 789 );
        $this->purgeClient
            ->expects( $this->once() )
            ->method( 'purge' )
            ->with( $locationIds )
            ->will( $this->returnArgument( 0 ) );

        $purger = new InstantCachePurger( $this->purgeClient );
        $this->assertSame( $locationIds, $purger->purge( $locationIds ) );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger::purgeAll
     */
    public function testPurgeAll()
    {
        $this->purgeClient
            ->expects( $this->once() )
            ->method( 'purgeAll' );

        $purger = new InstantCachePurger( $this->purgeClient );
        $purger->purgeAll();
    }
}
