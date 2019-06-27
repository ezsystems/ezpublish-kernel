<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Adapter;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Psr\Cache\CacheItemInterface;

/**
 * Internal proxy adapter to make sure to clear in-memory cache when needed.
 *
 * @intenral For type hinting inside eZ\Publish\Core\Persistence\Cache\*. For external, type hint on TagAwareAdapterInterface.
 */
class InMemoryClearingProxyAdapter implements TagAwareAdapterInterface
{
    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    private $pool;

    /** @var \eZ\Publish\Core\Persistence\Cache\inMemory\InMemoryCache[] */
    private $inMemoryPools;

    /**
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $pool
     * @param \eZ\Publish\Core\Persistence\Cache\inMemory\InMemoryCache[] $inMemoryPools
     */
    public function __construct(TagAwareAdapterInterface $pool, iterable $inMemoryPools)
    {
        $this->pool = $pool;
        $this->inMemoryPools = $inMemoryPools;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        return $this->pool->getItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        return $this->pool->getItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->pool->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        foreach ($this->inMemoryPools as $inMemory) {
            $inMemory->deleteMulti([$key]);
        }

        return $this->pool->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($this->inMemoryPools as $inMemory) {
            $inMemory->deleteMulti($keys);
        }

        return $this->pool->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
        // No tracking of tags in in-memory, as it's anyway meant to only optimize for reads (GETs) and not writes.
        foreach ($this->inMemoryPools as $inMemory) {
            $inMemory->clear();
        }

        return $this->pool->invalidateTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        foreach ($this->inMemoryPools as $inMemory) {
            $inMemory->clear();
        }

        return $this->pool->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        return $this->pool->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->pool->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return $this->pool->commit();
    }
}
