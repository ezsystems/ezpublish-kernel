<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryClearingProxyAdapter;
use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;

/**
 * Class AbstractInMemoryHandler.
 *
 * Abstract handler for use in other Persistence Cache Handlers.
 */
abstract class AbstractInMemoryHandler
{
    /**
     * NOTE: Instance of this must be InMemoryClearingProxyAdapter in order for cache clearing to affect in-memory cache.
     *
     * @var \eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryClearingProxyAdapter
     */
    protected $cache;

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
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryClearingProxyAdapter $cache
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     * @param \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache $inMemory
     */
    public function __construct(
        PersistenceHandler $persistenceHandler,
        InMemoryClearingProxyAdapter $cache,
        PersistenceLogger $logger,
        InMemoryCache $inMemory
    ) {
        $this->persistenceHandler = $persistenceHandler;
        $this->setCacheDependencies($cache, $logger, $inMemory);
        $this->init();
    }

    /**
     * @api May be used from handlers that needs to inject something else then PersistenceHandler to __construct(), to
     *      avoid having to have intimate knowledge of internal classes used by AbstractInMemoryHandler in signature.
     *
     * E.g:
     *    public function __construct(MyHandler $myHandler, ...$params) {
     *        $this->myHandler = $myHandler;
     *        $this->setCacheDependencies(...$params);
     *    }
     *
     * @param \eZ\Publish\Core\Persistence\Cache\Adapter\InMemoryClearingProxyAdapter $cache
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     * @param \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache $inMemory
     */
    protected function setCacheDependencies(
        InMemoryClearingProxyAdapter $cache,
        PersistenceLogger $logger,
        InMemoryCache $inMemory
    ) {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->inMemory = $inMemory;
    }

    /**
     * Optional function to initialize handler without having to overload __construct().
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
     * @return array
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
}
