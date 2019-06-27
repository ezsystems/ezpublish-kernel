<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\InMemory;

/**
 * Simple internal In-Memory Cache Pool.
 *
 * @internal Only for use in ..\AbstractInMemoryHandler & ..\Adapter\InMemoryClearingProxyAdapter, may change depending on needs there.
 *
 * Goal:
 * By nature caches stale objects in memory to avoid round-trips to cache backend over network.
 * Thus should only be used for meta data which changes in-frequently, and has limits and short expiry to keep the risk
 * of using out-of-date data to a minimum. Besides backend round trips, also aims to keep memory usage to a minimum.
 *
 * Properties:
 * - Limited by amount and time, to make sure to use minimal amount of memory, and reduce risk of stale cache.
 * - Secondary indexes, allows for populating memory quickly, and safely able to delete only knowing primary key
 *   E.g. I language object is loaded first by id, locale (eng-GB) lookup is also populated. Opposite is also true.
 * - On object limits, will first try to vacuum in-frequently used cache items, then by FIFO principle if needed.
 */
class InMemoryCache
{
    /** @var float Cache Time to Live, in seconds. This is only for how long we keep cache object around in-memory. */
    private $ttl;

    /** @var int The limit of objects in cache pool at a given time */
    private $limit;

    /** @var bool Switch for enabeling/disabling in-memory cache */
    private $enabled;

    /**
     * Cache objects by primary key.
     *
     * @var object[]
     */
    private $cache = [];

    /** @var float[] Expiry timestamp (float microtime) for individual cache (by primary key). */
    private $cacheExpiryTime = [];

    /** @var int[] Access counter for individual cache (by primary key), to order by by popularity on vacuum(). */
    private $cacheAccessCount = [];

    /**
     * Mapping of secondary index to primary key.
     *
     * @var string[]
     */
    private $cacheIndex = [];

    /**
     * In Memory Cache constructor.
     *
     * @param int $ttl Seconds for the cache to live, by default 300 milliseconds
     * @param int $limit Limit for values to keep in cache, by default 100 cache values.
     * @param bool $enabled For use by configuration to be able to disable or enable depending on needs.
     */
    public function __construct(int $ttl = 300, int $limit = 100, bool $enabled = true)
    {
        $this->ttl = $ttl / 1000;
        $this->limit = $limit;
        $this->enabled = $enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return bool Prior value
     */
    public function setEnabled(bool $enabled = true): bool
    {
        $was = $this->enabled;
        $this->enabled = $enabled;

        return $was;
    }

    /**
     * Returns a cache objects.
     *
     * @param string $key Primary or secondary index to look for cache on.
     *
     * @return object|null Object if found, null if not.
     */
    public function get(string $key)
    {
        if ($this->enabled === false) {
            return null;
        }

        $index = $this->cacheIndex[$key] ?? $key;
        if (!isset($this->cache[$index]) || $this->cacheExpiryTime[$index] < microtime(true)) {
            return null;
        }

        ++$this->cacheAccessCount[$index];

        return $this->cache[$index];
    }

    /**
     * Set object in in-memory cache.
     *
     * Should only set Cache hits here!
     *
     * @param object[] $objects
     * @param callable $objectIndexes Return array of indexes per object (first argument), must return at least 1 primary index
     * @param string|null $listIndex Optional index for list of items
     */
    public function setMulti(array $objects, callable $objectIndexes, string $listIndex = null): void
    {
        // If objects accounts for more then 20% of our limit, assume it's bulk load and skip saving in-memory
        if ($this->enabled === false || \count($objects) >= $this->limit / 5) {
            return;
        }

        // check if we will reach limit by adding these objects, if so remove old cache
        if (\count($this->cache) + \count($objects) >= $this->limit) {
            $this->vacuum();
        }

        $expiryTime = microtime(true) + $this->ttl;
        // if set add objects to cache on list index (typically a "all" key)
        if ($listIndex) {
            $this->cache[$listIndex] = $objects;
            $this->cacheExpiryTime[$listIndex] = $expiryTime;
            $this->cacheAccessCount[$listIndex] = 0;
        }

        foreach ($objects as $object) {
            // Skip if there are no indexes
            if (!$indexes = $objectIndexes($object)) {
                continue;
            }

            $key = \array_shift($indexes);
            $this->cache[$key] = $object;
            $this->cacheExpiryTime[$key] = $expiryTime;
            $this->cacheAccessCount[$key] = 0;

            foreach ($indexes as $index) {
                $this->cacheIndex[$index] = $key;
            }
        }
    }

    /**
     * Removes multiple in-memory cache from the pool.
     *
     * @param string[] $keys An array of keys that should be removed from the pool.
     */
    public function deleteMulti(array $keys): void
    {
        if ($this->enabled === false) {
            return;
        }

        foreach ($keys as $index) {
            if ($key = $this->cacheIndex[$index] ?? null) {
                unset($this->cacheIndex[$index]);
            } else {
                $key = $index;
            }

            unset($this->cache[$key], $this->cacheExpiryTime[$key], $this->cacheAccessCount[$key]);
        }
    }

    /**
     * Deletes all cache in the in-memory pool.
     */
    public function clear(): void
    {
        // On purpose does not check if enabled, in case of prior phase being enabled we clear cache when someone asks.
        $this->cache = $this->cacheIndex = $this->cacheExpiryTime = $this->cacheAccessCount = [];
    }

    /**
     * Call to reduce cache items when $limit has been reached.
     *
     * Deletes items using LFU approach where the least frequently used items are evicted from bottom and up.
     * Within groups of cache used equal amount of times, the oldest keys will be deleted first (FIFO).
     */
    private function vacuum(): void
    {
        // To not having to call this too often, we aim to clear 33% of cache values in bulk
        $deleteTarget = (int) ($this->limit / 3);

        // First we flip the cacheAccessCount and sort so order is first by access (LFU) then secondly by order (FIFO)
        $groupedAccessCount = [];
        foreach ($this->cacheAccessCount as $key => $accessCount) {
            $groupedAccessCount[$accessCount][] = $key;
        }
        \ksort($groupedAccessCount, SORT_NUMERIC);

        // Merge the resulting sorted array of arrays to flatten the result
        foreach (\array_merge(...$groupedAccessCount) as $key) {
            unset($this->cache[$key], $this->cacheExpiryTime[$key], $this->cacheAccessCount[$key]);
            --$deleteTarget;

            if ($deleteTarget <= 0) {
                break;
            }
        }

        // Cleanup secondary indexes for missing primary keys
        foreach ($this->cacheIndex as $index => $key) {
            if (!isset($this->cache[$key])) {
                unset($this->cacheIndex[$index]);
            }
        }
    }
}
