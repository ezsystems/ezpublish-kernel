<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class AbstractInMemoryHandler.
 *
 * Abstract handler for use in other Persistence Cache Handlers.
 */
abstract class AbstractInMemoryHandler
{
    /**
     * NOTE: $cache and $inMemory is private in order to abstract interactions across both,
     *       and be able to optimize this later without affecting all classes extending this.
     *
     * @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface
     */
    private $cache;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache
     */
    private $inMemory;

    /**
     * Setups current handler with everything needed.
     *
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cache
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     * @param \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache $inMemory
     */
    public function __construct(
        TagAwareAdapterInterface $cache,
        PersistenceHandler $persistenceHandler,
        PersistenceLogger $logger,
        InMemoryCache $inMemory
    ) {
        $this->cache = $cache;
        $this->persistenceHandler = $persistenceHandler;
        $this->logger = $logger;
        $this->inMemory = $inMemory;

        $this->init();
    }

    /**
     * Optional dunction to initialize handler without having to overload __construct().
     */
    protected function init(): void
    {
        // overload to add init logic if needed in handler
    }

    /**
     * Load one cache item from cache and loggs the hits / misses.
     *
     * Load items from in-memory cache, symfony cache pool or backend in that order.
     * If not cached the returned objects will be placed in cache.
     *
     * @param int|string $id
     * @param string $keyPrefix E.g "ez-content-"
     * @param callable $backendLoader Function for loading missing objects, gets array with missing id's as argument,
     *                                expects return value to be array with id as key. Missing items should be missing.
     * @param callable $cacheTagger Gets cache object as argument, return array of cache tags.
     * @param callable $cacheIndexes Gets cache object as argument, return array of cache keys.
     * @param string $keySuffix Optional, e.g "-by-identifier"
     *
     * @return object
     */
    final protected function getCacheValue(
        $id,
        string $keyPrefix,
        callable $backendLoader,
        callable $cacheTagger,
        callable $cacheIndexes,
        string $keySuffix = ''
    ) {
        $key = $keyPrefix . $id . $keySuffix;
        // In-memory
        if ($object = $this->inMemory->get($key)) {
            $this->logger->logCacheHit([$id], 3, true);

            return $object;
        }

        // Cache pool
        $cacheItem = $this->cache->getItem($key);
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit([$id], 3);
            $this->inMemory->setMulti([$object = $cacheItem->get()], $cacheIndexes);

            return $object;
        }

        // Backend
        $object = $backendLoader($id);
        $this->inMemory->setMulti([$object], $cacheIndexes);
        $this->logger->logCacheMiss([$id], 3);
        $this->cache->save(
            $cacheItem
                ->set($object)
                ->tag($cacheTagger($object))
        );

        return $object;
    }

    /**
     * Load list of objects of some type and loggs the hits / misses.
     *
     * Load items from in-memory cache, symfony cache pool or backend in that order.
     * If not cached the returned objects will be placed in cache.
     *
     * @param string $key
     * @param callable $backendLoader Function for loading ALL objects, value is cached as-is.
     * @param callable $cacheTagger Gets cache object as argument, return array of cache tags.
     * @param callable $cacheIndexes Gets cache object as argument, return array of cache keys.
     * @param callable $listTags Optional, global tags for the list cache.
     * @param array $arguments Optional, arguments when parnt method takes arguments that key varies on.
     *
     * @return object
     */
    final protected function getListCacheValue(
        string $key,
        callable $backendLoader,
        callable $cacheTagger,
        callable $cacheIndexes,
        callable $listTags = null,
        array $arguments = []
    ) {
        // In-memory
        if ($objects = $this->inMemory->get($key)) {
            $this->logger->logCacheHit($arguments, 3, true);

            return $objects;
        }

        // Cache pool
        $cacheItem = $this->cache->getItem($key);
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit($arguments, 3);
            $this->inMemory->setMulti($objects = $cacheItem->get(), $cacheIndexes, $key);

            return $objects;
        }

        // Backend
        $objects = $backendLoader();
        $this->inMemory->setMulti($objects, $cacheIndexes, $key);
        $this->logger->logCacheMiss($arguments, 3);

        if ($listTags !== null) {
            $tagSet = [$listTags()];
        } else {
            $tagSet = [[]];
        }

        foreach ($objects as $object) {
            $tagSet[] = $cacheTagger($object);
        }

        $this->cache->save(
            $cacheItem
                ->set($objects)
                ->tag(array_unique(array_merge(...$tagSet)))
        );

        return $objects;
    }

    /**
     * Load several cache items from cache and loggs the hits / misses.
     *
     * Load items from in-memory cache, symfony cache pool or backend in that order.
     * If not cached the returned objects will be placed in cache.
     *
     * Cache items must be stored with a key in the following format "${keyPrefix}${id}", like "ez-content-info-${id}",
     * in order for this method to be able to prefix key on id's and also extract key prefix afterwards.
     *
     * @param array $ids
     * @param string $keyPrefix E.g "ez-content-"
     * @param callable $backendLoader Function for loading missing objects, gets array with missing id's as argument,
     *                                expects return value to be array with id as key. Missing items should be missing.
     * @param callable $cacheTagger Gets cache object as argument, return array of cache tags.
     * @param callable $cacheIndexes Gets cache object as argument, return array of cache keys.
     * @param string $keySuffix Optional, e.g "-by-identifier"
     *
     * @return array
     */
    final protected function getMultipleCacheValues(
        array $ids,
        string $keyPrefix,
        callable $backendLoader,
        callable $cacheTagger,
        callable $cacheIndexes,
        string $keySuffix = ''
    ): array {
        if (empty($ids)) {
            return [];
        }

        // Generate unique cache keys and check if in-memory
        $list = [];
        $cacheKeys = [];
        $cacheKeysToIdMap = [];
        foreach (array_unique($ids) as $id) {
            $key = $keyPrefix . $id . $keySuffix;
            if ($object = $this->inMemory->get($key)) {
                $list[$id] = $object;
            } else {
                $cacheKeys[] = $key;
                $cacheKeysToIdMap[$key] = $id;
            }
        }

        // No in-memory misses
        if (empty($cacheKeys)) {
            $this->logger->logCacheHit($ids, 3, true);

            return $list;
        }

        // Load cache items by cache keys (will contain hits and misses)
        $loaded = [];
        $cacheMisses = [];
        foreach ($this->cache->getItems($cacheKeys) as $key => $cacheItem) {
            $id = $cacheKeysToIdMap[$key];
            if ($cacheItem->isHit()) {
                $list[$id] = $cacheItem->get();
                $loaded[$id] = $list[$id];
            } else {
                $cacheMisses[] = $id;
                $list[$id] = $cacheItem;
            }
        }

        // No cache pool misses, cache loaded items in-memory and return
        if (empty($cacheMisses)) {
            $this->logger->logCacheHit($ids, 3);
            $this->inMemory->setMulti($loaded, $cacheIndexes);

            return $list;
        }

        // Load missing items, save to cache & apply to list if found
        $backendLoadedList = $backendLoader($cacheMisses);
        foreach ($cacheMisses as $id) {
            if (isset($backendLoadedList[$id])) {
                $this->cache->save(
                    $list[$id]
                        ->set($backendLoadedList[$id])
                        ->tag($cacheTagger($backendLoadedList[$id]))
                );
                $loaded[$id] = $backendLoadedList[$id];
                $list[$id] = $backendLoadedList[$id];
            } else {
                // not found
                unset($list[$id]);
            }
        }

        $this->inMemory->setMulti($loaded, $cacheIndexes);
        unset($loaded, $backendLoadedList);
        $this->logger->logCacheMiss($cacheMisses, 3);

        return $list;
    }

    /**
     * Delete cache by keys.
     *
     * @param array $keys
     */
    final protected function deleteCache(array $keys): void
    {
        if (empty($keys)) {
            return;
        }

        $this->inMemory->deleteMulti($keys);
        $this->cache->deleteItems($keys);
    }

    /**
     * Invalidate cache by tags.
     *
     * @param array $tags
     */
    final protected function invalidateCache(array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $this->inMemory->clear();
        $this->cache->invalidateTags($tags);
    }

    /**
     * Clear ALL cache.
     *
     * Only use this in extname situations, or for isolation in tests.
     */
    final protected function clearCache(): void
    {
        $this->inMemory->clear();
        $this->cache->clear();
    }
}
