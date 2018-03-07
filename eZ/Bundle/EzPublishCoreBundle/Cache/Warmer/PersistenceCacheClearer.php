<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Cache\Warmer;

use eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Clears Persistence Cache during warm-up.
 */
class PersistenceCacheClearer implements CacheWarmerInterface
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator
     */
    private $cache;

    /**
     * @param \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator $cache
     */
    public function __construct(CacheServiceDecorator $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * Optional warmers can be ignored on certain conditions.
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return bool true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * Clear Persistence Cache (useful for Cache handled by Redis or other external service).
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->cache->clear();
    }
}
