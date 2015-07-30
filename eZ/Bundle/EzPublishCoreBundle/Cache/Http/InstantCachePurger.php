<?php

/**
 * File containing the InstantCachePurger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
    public function clear($cacheDir)
    {
        $this->purgeAll();
    }
}
