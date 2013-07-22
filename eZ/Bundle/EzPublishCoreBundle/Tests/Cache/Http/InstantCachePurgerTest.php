<?php
/**
 * File containing the InstantCachePurgerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Cache\Http;

use eZ\Bundle\EzPublishCoreBundle\Cache\Http\InstantCachePurger;
use eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\InstantCachePurgerTest as BaseTest;

class InstantCachePurgerTest extends BaseTest
{
    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Cache\Http\InstantCachePurger::clear
     */
    public function testClear()
    {
        $this
            ->purgeClient
            ->expects( $this->once() )
            ->method( 'purgeAll' );

        $purger = new InstantCachePurger( $this->purgeClient );
        $purger->clear( 'cache/dir/' );
    }
}
