<?php

/**
 * File containing the CacheServiceDecorator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache;

use Stash\Interfaces\PoolInterface;

/**
 * Class CacheServiceDecorator.
 *
 * Wraps the Cache Service for Spi cache to apply key prefix for the cache
 */
class CacheServiceDecorator
{
    const SPI_CACHE_KEY_PREFIX = 'ez_spi';

    /**
     * @var \Stash\Interfaces\PoolInterface
     */
    protected $cachePool;

    /**
     * Constructs the cache service decorator.
     *
     * @param \Stash\Interfaces\PoolInterface $cachePool
     */
    public function __construct(PoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * Prepend key with prefix and support array format Stash supported before.
     *
     * {@see \Psr\Cache\CacheItemPoolInterface}
     *
     * @internal param array|string $key , $key, $key...
     *
     * @return \Stash\Interfaces\ItemInterface
     */
    public function getItem()
    {
        $args = func_get_args();

        if (empty($args)) {
            return $this->cachePool->getItem(self::SPI_CACHE_KEY_PREFIX);
        }

        //  Upstream seems to no longer support array, so we flatten it
        if (!isset($args[1]) && is_array($args[0])) {
            $key = implode('/', array_map([$this, 'washKey'], $args[0]));
        } else {
            $key = '' . implode('/', array_map([$this, 'washKey'], $args));
        }

        $key = $key === '' ? self::SPI_CACHE_KEY_PREFIX : self::SPI_CACHE_KEY_PREFIX . '/' . $key;

        return $this->cachePool->getItem($key);
    }

    /**
     * Prepend keys with prefix.
     *
     * {@see \Psr\Cache\CacheItemPoolInterface}
     *
     * @param array $keys
     * @return \Stash\Interfaces\ItemInterface[]
     */
    public function getItems(array $keys = array())
    {
        $prefix = self::SPI_CACHE_KEY_PREFIX;
        $keys = array_map(
            function ($key) use ($prefix) {
                $key = $this->washKey($key);

                return $key === '' ? $prefix : $prefix . '/' . $key;
            },
            $keys
        );

        return $this->cachePool->getItems($keys);
    }

    /**
     * Remove slashes from start and end of keys, and for content replace it with _ to avoid issues for Stash.
     *
     * @param string $key
     * @return string
     */
    private function washKey($key)
    {
        return str_replace('/', '_', trim($key, '/'));
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @internal param array|null|string $key , $key, $key...
     */
    public function clear()
    {
        $item = call_user_func_array(array($this, 'getItem'), func_get_args());

        return $item->clear();
    }
}
