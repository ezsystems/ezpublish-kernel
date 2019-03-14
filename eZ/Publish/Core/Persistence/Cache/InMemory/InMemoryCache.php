<?php

/**
 * File containing MetadataCachePool class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\InMemory;

/**
 * Simple In-Memory Cache Pool.
 *
 * @internal Only for use in eZ\Publish\Core\Persistence\Cache\AbstractInMemoryHandler, may change depending on needs there.
 */
class InMemoryCache
{
    /**
     * @var float Cache Time to Live, in seconds. This is only for how long we keep cache object around in-memory.
     */
    private $ttl;

    /**
     * @var int The limit of objects in cache pool at a given time
     */
    private $limit;

    /**
     * @var bool Switch for enabeling/disabling in-memory cache
     */
    private $enabled;

    /**
     * Cache objects by primary key.
     *
     * @var object[]
     */
    private $cache = [];

    /**
     * @var float[] Timestamp (float microtime) for individual cache by primary key.
     */
    private $cacheTime = [];

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
     * @param int $limit Limit for values to keep in cache, by default 100 cache values (per pool instance).
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
        if (!isset($this->cache[$index]) || $this->cacheTime[$index] + $this->ttl < microtime(true)) {
            return null;
        }

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
        // If objects accounts for more then 20% of our limit, assume it's bulk content load and skip saving in-memory
        if ($this->enabled === false || \count($objects) >= $this->limit / 5) {
            return;
        }

        // check if we will reach limit by adding these objects, if so remove old cache
        if (\count($this->cache) + \count($objects) >= $this->limit) {
            $this->vacuum();
        }

        $time = microtime(true);
        // if set add objects to cache on list index (typically a "all" key)
        if ($listIndex) {
            $this->cache[$listIndex] = $objects;
            $this->cacheTime[$listIndex] = $time;
        }

        foreach ($objects as $object) {
            // Skip if there are no indexes
            if (!$indexes = $objectIndexes($object)) {
                continue;
            }

            $key = array_shift($indexes);
            $this->cache[$key] = $object;
            $this->cacheTime[$key] = $time;

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

        foreach ($keys as $key) {
            if ($index = $this->cacheIndex[$key] ?? null) {
                unset($this->cacheIndex[$key], $this->cache[$index], $this->cacheTime[$index]);
            } else {
                unset($this->cache[$key], $this->cacheTime[$key]);
            }
        }
    }

    /**
     * Deletes all cache in the in-memory pool.
     */
    public function clear(): void
    {
        // On purpose does not check if enabled, in case of several instances we allow clearing cache
        $this->cache = $this->cacheIndex = $this->cacheTime = [];
    }

    /**
     * Call to reduce cache items when $limit has been reached.
     *
     * Deletes expired first, then oldest(or least used?).
     */
    private function vacuum(): void
    {
        // Vacuuming cache in bulk, clearing the 33% oldest cache values
        $this->cache = \array_slice($this->cache, (int) ($this->limit / 3));

        // Cleanup secondary index and cache time for missing primary keys
        foreach ($this->cacheIndex as $index => $key) {
            if (!isset($this->cache[$key])) {
                unset($this->cacheIndex[$index], $this->cacheTime[$key]);
            }
        }
    }
}
