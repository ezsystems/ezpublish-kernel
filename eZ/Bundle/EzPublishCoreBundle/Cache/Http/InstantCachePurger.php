<?php
/**
 * File containing the InstantCachePurger class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\InstantCachePurger as BasePurger;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class InstantCachePurger extends BasePurger implements CacheClearerInterface
{
    /**
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory.
     */
    public function clear( $cacheDir )
    {
        $this->purgeAll();
    }
}
