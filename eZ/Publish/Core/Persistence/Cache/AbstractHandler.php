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
     * @param array $ids
     * @param string $keyPrefix
     *
     * @return array Format [id[] $cacheMisses, CacheItem[<id>] $list], list contains hits & misses (if there where any).
     */
    final protected function getMultipleCacheItems(array $ids, string $keyPrefix): array
    {
        if (empty($ids)) {
            return [[], []];
        }

        $cacheKeys = [];
        foreach (array_unique($ids) as $id) {
            $cacheKeys[] = $keyPrefix . $id;
        }

        $list = [];
        $cacheMisses = [];
        $keyPrefixLength = strlen($keyPrefix);
        foreach ($this->cache->getItems($cacheKeys) as $key => $cacheItem) {
            $id = substr($key, $keyPrefixLength);
            if ($cacheItem->isHit()) {
                $list[$id] = $cacheItem->get();
                continue;
            }

            $cacheMisses[] = $id;
            $list[$id] = $cacheItem;
        }

        return [$cacheMisses, $list];
    }
}
