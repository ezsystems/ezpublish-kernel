<?php

/**
 * File containing the ContentHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Class AbstractHandler.
 *
 * Abstract handler for use in other Persistence Cache Handlers.
 */
abstract class AbstractHandler
{
    /**
     * @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface
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
     * Setups current handler with everything needed.
     *
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cache
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function __construct(
        TagAwareAdapterInterface $cache,
        PersistenceHandler $persistenceHandler,
        PersistenceLogger $logger
    ) {
        $this->cache = $cache;
        $this->persistenceHandler = $persistenceHandler;
        $this->logger = $logger;
    }

    /**
     * Helper for getting multiple cache items in one call and do the id extraction for you.
     *
     * Cache items must be stored with a key in the following format "${keyPrefix}${id}", like "ez-content-info-${id}",
     * in order for this method to be able to prefix key on id's and also extract key prefix afterwards.
     *
     * It also optionally supports a key suffixs, for use on a variable argument that affects all lookups,
     * like translations, i.e. "ez-content-${id}-${translationKey}" where $keySuffixes = [$id => "-${translationKey}"].
     *
     * @param array $ids
     * @param string $keyPrefix E.g "ez-content-"
     * @param callable $missingLoader Function for loading missing objects, gets array with missing id's as argument,
     *                                expects return value to be array with id as key. Missing items should be missing.
     * @param callable $loadedTagger Function for tagging loaded object, gets object as argument, return array of tags.
     * @param string|array $keySuffix Optional, if array key is id as provided in $ids, and value (when string or array)
     *                                is a key suffix e.g. "-eng-Gb"
     *
     * @return array
     */
    final protected function getMultipleCacheItems(
        array $ids,
        string $keyPrefix,
        callable $missingLoader,
        callable $loadedTagger,
        $keySuffix = ''
    ): array {
        if (empty($ids)) {
            return [];
        }

        // Generate unique cache keys
        $cacheKeys = [];
        foreach (array_unique($ids) as $id) {
            $cacheKeys[] = $keyPrefix . $id . (is_array($keySuffix) ? $keySuffix[$id] ?? '' : ($keySuffix ?: ''));
        }

        // Load cache items by cache keys (will contain hits and misses)
        $list = [];
        $cacheMisses = [];
        $keyPrefixLength = strlen($keyPrefix);
        foreach ($this->cache->getItems($cacheKeys) as $key => $cacheItem) {
            $id = substr($key, $keyPrefixLength);
            if (!empty($keySuffix)) {
                $id = explode('-', $id, 2)[0];
            }

            if ($cacheItem->isHit()) {
                $list[$id] = $cacheItem->get();
            } else {
                $cacheMisses[] = $id;
                $list[$id] = $cacheItem;
            }
        }

        // No misses, return completely cached list
        if (empty($cacheMisses)) {
            return $list;
        }

        // Load missing items, save to cache & apply to list if found
        $loadedList = $missingLoader($cacheMisses);
        foreach ($cacheMisses as $id) {
            if (isset($loadedList[$id])) {
                $this->cache->save(
                    $list[$id]
                        ->set($loadedList[$id])
                        ->tag($loadedTagger($loadedList[$id]))
                );
                $list[$id] = $loadedList[$id];
            } else {
                unset($list[$id]);
            }
        }

        return $list;
    }
}
