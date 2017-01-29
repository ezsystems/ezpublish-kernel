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
     * Helper for getting multiple cache items.
     *
     * @param array $ids
     * @param string $keyPrefix
     *
     * @return array 2 arrays returned as [$cacheMissIds, $list], where list contains objects / cache misses, key by id.
     */
    final protected function getMultipleCacheItems(array $ids, string $keyPrefix) : array
    {
        if (empty($ids)) {
            return [[], []];
        }

        $cacheKeys = [];
        foreach (array_unique($ids) as $id) {
            $cacheKeys[] = $keyPrefix.$id;
        }

        $list = [];
        $cacheMissIds = [];
        $keyPrefixLength = strlen($keyPrefix);
        foreach($this->cache->getItems($cacheKeys) as $key => $cacheItem) {
            $id = substr($key, $keyPrefixLength);
            if ($cacheItem->isHit()) {
                $list[$id] = $cacheItem->get();
                continue;
            }

            $cacheMissIds[] = $id;
            $list[$id] = $cacheItem;
        }

        return [$cacheMissIds, $list];
    }
}
