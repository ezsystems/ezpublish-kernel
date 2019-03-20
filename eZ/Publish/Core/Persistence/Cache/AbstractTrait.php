<?php

/**
 * File containing the AbstractTrait.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

/**
 * Trait AbstractTrait.
 *
 * @internal Only for use within this namespace.
 *
 * @property \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cache
 */
trait AbstractTrait
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * Helper for getting multiple cache items in one call and do the id extraction for you.
     *
     * Cache items must be stored with a key in the following format "${keyPrefix}${id}", like "ez-content-info-${id}",
     * in order for this method to be able to prefix key on id's and also extract key prefix afterwards.
     *
     * @param array $ids
     * @param string $keyPrefix E.g "ez-content-"
     * @param callable $backendLoader Function for loading missing objects, gets array with missing id's as argument,
     *                                expects return value to be array with id as key. Missing items should be missing.
     * @param callable $cacheTagger Function for tagging loaded object, gets object as argument, return array of tags.
     * @param string $keySuffix Optional, e.g "-by-identifier"
     *
     * @return array
     */
    final protected function getMultipleCacheValues(
        array $ids,
        string $keyPrefix,
        callable $backendLoader,
        callable $cacheTagger,
        string $keySuffix = ''
    ): array {
        if (empty($ids)) {
            return [];
        }

        // Generate unique cache keys
        $cacheKeys = [];
         $cacheKeysToIdMap = [];
        foreach (\array_unique($ids) as $id) {
            $key = $keyPrefix . $id . $keySuffix;
            $cacheKeys[] = $key;
            $cacheKeysToIdMap[$key] = $id;
        }

        // Load cache items by cache keys (will contain hits and misses)
        $list = [];
        $cacheMisses = [];
        foreach ($this->cache->getItems($cacheKeys) as $key => $cacheItem) {
            $id = $cacheKeysToIdMap[$key];
            if ($cacheItem->isHit()) {
                $list[$id] = $cacheItem->get();
            } else {
                $cacheMisses[] = $id;
                $list[$id] = $cacheItem;
            }
        }

        // No misses, return completely cached list
        if (empty($cacheMisses)) {
            $this->logger->logCacheHit($ids, 3);

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
                $list[$id] = $backendLoadedList[$id];
            } else {
                // not found
                unset($list[$id]);
            }
        }

        $this->logger->logCacheMiss($cacheMisses, 3);

        return $list;
    }
}
