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
use Symfony\Component\Stopwatch\Stopwatch;

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
     * @var null|Stopwatch
     */
    protected $stopwatch;

    /**
     * Constructs the cache service decorator.
     *
     * @param \Stash\Interfaces\PoolInterface $cachePool
     * @param null|Stopwatch $stopwatch
     */
    public function __construct(PoolInterface $cachePool, Stopwatch $stopwatch = null)
    {
        $this->cachePool = $cachePool;
        $this->stopwatch = $stopwatch;
    }

    /**
     * Returns a Cache item for the specified key. The key can be either a series of string arguments,
     * or an array.
     *
     * @internal param array|string $key , $key, $key...
     *
     * @return \Stash\Interfaces\ItemInterface
     */
    public function getItem()
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start('getItem', 'ez.spi.cache');
        }

        $args = func_get_args();

        // check to see if a single array was used instead of multiple arguments, & check empty in case of empty clear()
        if (empty($args)) {
            $args = array();
        } elseif (!isset($args[1]) && is_array($args[0])) {
            $args = $args[0];
        }

        array_unshift($args, self::SPI_CACHE_KEY_PREFIX);

        $item = $this->cachePool->getItem($args);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop('getItem');
        }

        return $item;
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @internal param array|null|string $key , $key, $key...
     */
    public function clear($arg1 = null)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(($arg1 ? 'clearItem' : 'clearAll'), 'ez.spi.cache');
        }

        $item = call_user_func_array(array($this, 'getItem'), func_get_args());
        $return = $item->clear();

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(($arg1 ? 'clearItem' : 'clearAll'));
        }

        return $return;
    }
}
