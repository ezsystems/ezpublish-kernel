<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Adapter;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Internal proxy adapter invalidating our isolated in-memory cache, and defer shared pool changes during transactions.
 *
 * @internal For type hinting inside eZ\Publish\Core\Persistence\Cache\*. For external, type hint on TagAwareAdapterInterface.
 */
class TransactionalInMemoryCacheAdapter implements TransactionAwareAdapterInterface
{
    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    protected $sharedPool;

    /** @var \eZ\Publish\Core\Persistence\Cache\inMemory\InMemoryCache[] */
    private $inMemoryPools;

    /** @var int */
    protected $transactionDepth;

    /** @var array To be unique and simplify lookup hash key is cache tag, value is only true value */
    protected $deferredTagsInvalidation;

    /** @var array To be unique and simplify lookup hash key is cache key, value is only true value */
    protected $deferredItemsDeletion;

    /** @var \Closure Callback for use by {@see markItemsAsDeferredMissIfNeeded()} when items are misses by deferred action */
    protected $setCacheItemAsMiss;

    /**
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $sharedPool
     * @param \eZ\Publish\Core\Persistence\Cache\inMemory\InMemoryCache[] $inMemoryPools
     * @param int $transactionDepth
     * @param array $deferredTagsInvalidation
     * @param array $deferredItemsDeletion
     */
    public function __construct(
        TagAwareAdapterInterface $sharedPool,
        iterable $inMemoryPools,
        int $transactionDepth = 0,
        array $deferredTagsInvalidation = [],
        array $deferredItemsDeletion = []
    ) {
        $this->sharedPool = $sharedPool;
        $this->inMemoryPools = $inMemoryPools;
        $this->transactionDepth = $transactionDepth;
        $this->deferredTagsInvalidation = empty($deferredTagsInvalidation) ? [] : \array_fill_keys($deferredTagsInvalidation, true);
        $this->deferredItemsDeletion = empty($deferredItemsDeletion) ? [] : \array_fill_keys($deferredItemsDeletion, true);
        // To modify protected $isHit when items are a "miss" based on deferred delete/invalidation during transactions
        $this->setCacheItemAsMiss = \Closure::bind(
            static function (CacheItem $item) {
                // ... Might not work for anything but new items
                $item->isHit = false;
            },
            null,
            CacheItem::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        return $this->markItemsAsDeferredMissIfNeeded(
            [$this->sharedPool->getItem($key)]
        )[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        return $this->markItemsAsDeferredMissIfNeeded(
            $this->sharedPool->getItems($keys)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        if (isset($this->deferredItemsDeletion[$key])) {
            return false;
        }

        return $this->sharedPool->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        foreach ($this->inMemoryPools as $inMemory) {
            $inMemory->deleteMulti([$key]);
        }

        if ($this->transactionDepth > 0) {
            $this->deferredItemsDeletion[$key] = true;

            return true;
        }

        return $this->sharedPool->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($this->inMemoryPools as $inMemory) {
            $inMemory->deleteMulti($keys);
        }

        if ($this->transactionDepth > 0) {
            $this->deferredItemsDeletion += \array_fill_keys($keys, true);

            return true;
        }

        return $this->sharedPool->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
        // No tracking of tags in in-memory, as it's anyway meant to only optimize for reads (GETs) and not writes.
        $this->clearInMemoryPools();

        if ($this->transactionDepth > 0) {
            $this->deferredTagsInvalidation += \array_fill_keys($tags, true);

            return true;
        }

        return $this->sharedPool->invalidateTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->clearInMemoryPools();

        // @todo Should we trow RunTime error or add support deferring full cache clearing?
        $this->transactionDepth = 0;
        $this->deferredItemsDeletion = [];
        $this->deferredTagsInvalidation = [];

        return $this->sharedPool->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        if ($this->transactionDepth > 0) {
            $this->deferredItemsDeletion[$item->getKey()] = true;

            return true;
        }

        return $this->sharedPool->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        ++$this->transactionDepth;
    }

    /**
     * {@inheritdoc}
     */
    public function commitTransaction(): void
    {
        if ($this->transactionDepth === 0) {
            // ignore, might have been a previous rollback
            return;
        }

        --$this->transactionDepth;

        // Once we reach 0 transaction count, sent out deferred deletes/invalidations to shared pool
        if ($this->transactionDepth === 0) {
            if (!empty($this->deferredItemsDeletion)) {
                $this->sharedPool->deleteItems(\array_keys($this->deferredItemsDeletion));
                $this->deferredItemsDeletion = [];
            }

            if (!empty($this->deferredTagsInvalidation)) {
                $this->sharedPool->invalidateTags(\array_keys($this->deferredTagsInvalidation));
                $this->deferredTagsInvalidation = [];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollbackTransaction(): void
    {
        $this->transactionDepth = 0;
        $this->deferredItemsDeletion = [];
        $this->deferredTagsInvalidation = [];

        $this->clearInMemoryPools();
    }

    /**
     * {@inheritdoc}
     *
     * Symfony cache feature for deferring saves, not used by eZ & not related to transaction handling here.
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->sharedPool->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     *
     * Symfony cache feature for committing deferred saves, not used by eZ & not related to transaction handling here.
     */
    public function commit()
    {
        return $this->sharedPool->commit();
    }

    /**
     * For use by getItem(s) to mark items as a miss if it's going to be cleared on transaction commit.
     *
     * @param \Symfony\Component\Cache\CacheItem[] $items
     *
     * @return \Symfony\Component\Cache\CacheItem[]
     */
    private function markItemsAsDeferredMissIfNeeded(iterable $items)
    {
        if ($this->transactionDepth === 0) {
            return $items;
        }

        // In case of $items being generator we map items over to new array as it can't be iterated several times
        $iteratedItems = [];
        $fnSetCacheItemAsMiss = $this->setCacheItemAsMiss;
        foreach ($items as $key => $item) {
            $iteratedItems[$key] = $item;

            if (!$item->isHit()) {
                continue;
            }

            if ($this->itemIsDeferredMiss($item)) {
                $fnSetCacheItemAsMiss($item);
            }
        }

        return $iteratedItems;
    }

    /**
     * @param \Symfony\Component\Cache\CacheItem $item
     *
     * @return bool
     */
    private function itemIsDeferredMiss(CacheItem $item): bool
    {
        if (isset($this->deferredItemsDeletion[$item->getKey()])) {
            return true;
        }

        foreach ($item->getPreviousTags() as $tag) {
            if (isset($this->deferredTagsInvalidation[$tag])) {
                return true;
            }
        }

        return false;
    }

    private function clearInMemoryPools(): void
    {
        foreach ($this->inMemoryPools as $inMemory) {
            $inMemory->clear();
        }
    }
}
