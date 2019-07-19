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
 * Internal proxy adapter invalidating cache items on transaction commits/rollbacks.
 */
class TransactionalCacheAdapterDecorator implements TransactionAwareAdapterInterface
{
    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    protected $innerPool;

    /** @var int */
    protected $transactionDepth;

    /** @var array */
    protected $deferredTagsInvalidation;

    /** @var array */
    protected $deferredItemsDeletion;

    /**
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $innerPool
     * @param int $transactionDepth
     * @param array $deferredTagsInvalidation
     * @param array $deferredItemsDeletion
     */
    public function __construct(
        TagAwareAdapterInterface $innerPool,
        int $transactionDepth = 0,
        array $deferredTagsInvalidation = [],
        array $deferredItemsDeletion = []
    ) {
        $this->innerPool = $innerPool;
        $this->transactionDepth = $transactionDepth;
        $this->deferredTagsInvalidation = $deferredTagsInvalidation;
        $this->deferredItemsDeletion = $deferredItemsDeletion;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        return $this->innerPool->getItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        return $this->innerPool->getItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->innerPool->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        if ($this->transactionDepth > 0) {
            $this->deferredItemsDeletion[$this->transactionDepth][] = $key;

            return true;
        }

        return $this->innerPool->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        if ($this->transactionDepth > 0) {
            $this->deferredItemsDeletion[$this->transactionDepth] = array_merge(
                $this->deferredItemsDeletion[$this->transactionDepth],
                $keys
            );

            return true;
        }

        return $this->innerPool->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags)
    {
        if ($this->transactionDepth > 0) {
            $this->deferredTagsInvalidation[$this->transactionDepth] = array_merge(
                $this->deferredTagsInvalidation[$this->transactionDepth],
                $tags
            );

            return true;
        }

        return $this->innerPool->invalidateTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->deferredItemsDeletion = [];
        $this->deferredTagsInvalidation = [];
        $this->transactionDepth = 0;

        return $this->innerPool->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        return $this->innerPool->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->innerPool->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return $this->innerPool->commit();
    }

    public function startTransaction(): void
    {
        ++$this->transactionDepth;
        $this->deferredTagsInvalidation[$this->transactionDepth] = [];
        $this->deferredItemsDeletion[$this->transactionDepth] = [];
    }

    public function stopTransaction(): void
    {
        if ($this->transactionDepth === 0) {
            return;
        }

        $this->invalidateDeferredTags();
        $this->deleteDeferredItems();

        unset(
            $this->deferredItemsDeletion[$this->transactionDepth],
            $this->deferredTagsInvalidation[$this->transactionDepth]
        );

        --$this->transactionDepth;
    }

    protected function invalidateDeferredTags(): void
    {
        $tags = $this->deferredTagsInvalidation[$this->transactionDepth];

        $this->innerPool->invalidateTags(array_unique($tags));
    }

    protected function deleteDeferredItems(): void
    {
        $keys = $this->deferredItemsDeletion[$this->transactionDepth];

        $this->innerPool->deleteItems(array_unique($keys));
    }
}
