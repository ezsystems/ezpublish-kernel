<?php

/**
 * File containing the ContentHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Adapter;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use Psr\Cache\CacheItemInterface;

/**
 * Proxy adapter to make sure to clear in-memory cache when needed.
 */
final class InMemoryClearingProxyAdapter implements TagAwareAdapterInterface
{
    /**
     * @var TagAwareAdapterInterface
     */
    private $pool;

    /**
     * @var InMemoryCache
     */
    private $inMemory;

    /**
     * @var int
     */
    private $ttl;

    public function __construct(TagAwareAdapterInterface $pool, InMemoryCache $inMemory)
    {
        $this->pool = $pool;
        $this->inMemory = $inMemory;
    }

    public function getItem($key)
    {
        return $this->pool->getItem($key);
    }

    public function getItems(array $keys = [])
    {
        return $this->pool->getItems($keys);
    }

    public function hasItem($key)
    {
        return $this->pool->hasItem($key);
    }

    public function deleteItem($key)
    {
        $this->inMemory->deleteMulti([$key]);

        return $this->pool->deleteItem($key);
    }

    public function deleteItems(array $keys)
    {
        $this->inMemory->deleteMulti($keys);

        return $this->pool->deleteItems($keys);
    }

    public function invalidateTags(array $tags)
    {
        // No tracking of tags in in-memory, as it's anyway meant to only optimize for reads (GETs) and not writes.
        $this->inMemory->clear();

        return $this->pool->invalidateTags($tags);
    }

    public function clear()
    {
        $this->inMemory->clear();

        return $this->pool->clear();
    }

    public function save(CacheItemInterface $item)
    {
        return $this->pool->save($item);
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->pool->saveDeferred($item);
    }

    public function commit()
    {
        return $this->pool->commit();
    }
}
