<?php

/**
 * File containing the CacheServiceDecorator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\Adapter\TransactionAwareAdapterInterface;
use eZ\Publish\Core\Persistence\Cache\Adapter\TransactionItem;
use Stash\Interfaces\PoolInterface;
use Tedivm\StashBundle\Service\CacheItem;

/**
 * Class CacheServiceDecorator.
 *
 * Wraps the Cache Service for Spi cache to apply key prefix for the cache
 */
class CacheServiceDecorator implements TransactionAwareAdapterInterface
{
    const SPI_CACHE_KEY_PREFIX = 'ez_spi';

    /**
     * @var \Stash\Interfaces\PoolInterface
     */
    protected $cachePool;

    /** @var int */
    protected $transactionDepth = 0;

    /** @var array */
    protected $deferredClear = [];

    /**
     * Constructs the cache service decorator.
     *
     * @param \Stash\Interfaces\PoolInterface $cachePool
     */
    public function __construct(PoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * Prepend key with prefix and support array format Stash supported before.
     *
     * {@see \Psr\Cache\CacheItemPoolInterface}
     *
     * @internal param array|string $key , $key, $key...
     *
     * @return \Stash\Interfaces\ItemInterface
     */
    public function getItem()
    {
        $args = func_get_args();

        if (empty($args)) {
            return $this->handleTransactionItems(
                [$this->cachePool->getItem(self::SPI_CACHE_KEY_PREFIX)]
            )[0];
        }

        //  Upstream seems to no longer support array, so we flatten it
        if (!isset($args[1]) && is_array($args[0])) {
            $key = implode('/', array_map([$this, 'washKey'], $args[0]));
        } else {
            $key = '' . implode('/', array_map([$this, 'washKey'], $args));
        }

        $key = $key === '' ? self::SPI_CACHE_KEY_PREFIX : self::SPI_CACHE_KEY_PREFIX . '/' . $key;

        return $this->handleTransactionItems(
            [$this->cachePool->getItem($key)]
        )[0];
    }

    /**
     * Prepend keys with prefix.
     *
     * {@see \Psr\Cache\CacheItemPoolInterface}
     *
     * @param array $keys
     * @return \Stash\Interfaces\ItemInterface[]
     */
    public function getItems(array $keys = [])
    {
        $prefix = self::SPI_CACHE_KEY_PREFIX;
        $keys = array_map(
            function ($key) use ($prefix) {
                $key = $this->washKey($key);

                return $key === '' ? $prefix : $prefix . '/' . $key;
            },
            $keys
        );

        return $this->handleTransactionItems(
            $this->cachePool->getItems($keys)
        );
    }

    /**
     * @param Item[] $items
     *
     * @return Item[]
     */
    private function handleTransactionItems(array $items)
    {
        if ($this->transactionDepth === 0) {
            return $items;
        }

        // If in transaction we set callback for save()/clear(), & for isMiss() detect if key is a deferred miss
        foreach ($items as $item) {
            /* @var TransactionItem $item */
            $item->setClearCallback(function ($key) {
                $this->rawClear([$key]);
            });
            $item->setIsClearedCallback(function ($key) {
                // Due to keys in Stash being hierarchical we need to check if key or prefix of key has been cleared
                foreach ($this->deferredClear as $clearedKey) {
                    if ($key === $clearedKey || stripos($key, $clearedKey) === 0) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $items;
    }

    /**
     * Remove slashes from start and end of keys, and for content replace it with _ to avoid issues for Stash.
     *
     * @param string $key
     * @return string
     */
    private function washKey($key)
    {
        return str_replace('/', '_', trim($key, '/'));
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @internal
     * @param array|null|string $key , $key, $key...
     * @return bool
     */
    public function clear(...$key)
    {
        // Make washed string key out of the arguments
        if (empty($key)) {
            $key = self::SPI_CACHE_KEY_PREFIX;
        } elseif (!isset($key[1]) && is_array($key[0])) {
            $key = self::SPI_CACHE_KEY_PREFIX . '/' . implode('/', array_map([$this, 'washKey'], $key[0]));
        } else {
            $key = self::SPI_CACHE_KEY_PREFIX . '/' . implode('/', array_map([$this, 'washKey'], $key));
        }

        return $this->rawClear([$key]);
    }

    private function rawClear(array $keys)
    {
        // Store for later if in transaction
        if ($this->transactionDepth) {
            $this->deferredClear = array_merge($this->deferredClear, $keys);

            return true;
        }

        return $this->executeClear($keys);
    }

    private function executeClear(array $keys)
    {
        // Detect full cache clear, if so ignore everything else
        if (in_array(self::SPI_CACHE_KEY_PREFIX, $keys, true)) {
            $item = $this->cachePool->getItem(self::SPI_CACHE_KEY_PREFIX);

            return $item->clear();
        }

        return $this->cachePool->deleteItems($keys);
    }

    /**
     * {@inheritdoc}.
     */
    public function beginTransaction()
    {
        if ($this->transactionDepth === 0) {
            // Wrap Item(s) in order to also handle calls to $item->save() and $item->clear()
            $this->cachePool->setItemClass(TransactionItem::class);
        }
        ++$this->transactionDepth;
    }

    /**
     * {@inheritdoc}.
     */
    public function commitTransaction()
    {
        if ($this->transactionDepth === 0) {
            // ignore, might have been a rollback
            return;
        }

        --$this->transactionDepth;

        // Cache commit time, it's now time to share the changes with the pool
        if ($this->transactionDepth === 0) {
            $this->cachePool->setItemClass(CacheItem::class);
            if (!empty($this->deferredClear)) {
                $this->executeClear($this->deferredClear);
                $this->deferredClear = [];
            }
        }
    }

    /**
     * {@inheritdoc}.
     */
    public function rollbackTransaction()
    {
        // A rollback in SQL will by default set transaction level to 0 & wipe transaction changes, so we do the same.
        $this->cachePool->setItemClass(CacheItem::class);
        $this->transactionDepth = 0;
        $this->deferredClear = [];
    }
}
