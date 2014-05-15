<?php
/**
 * File containing the InstantCachePurgerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
