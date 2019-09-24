<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Adapter\Item;

use Tedivm\StashBundle\Service\CacheItem;

/**
 * Class TransactionItem.
 *
 * Custom Item class for use during transactions, routes calls to save() and clear() to clear callback.
 *
 * @internal
 */
class TransactionItem extends CacheItem
{
    /** @var callable */
    private $clearFn;

    /** @var callable */
    private $isItemDeferedClearedFn;

    public function setClearCallback(callable $clear)
    {
        $this->clearFn = $clear;
    }

    public function setIsClearedCallback(callable $isItemDeferedCleared)
    {
        $this->isItemDeferedClearedFn = $isItemDeferedCleared;
    }

    /**
     * {@inheritdoc}
     */
    public function isMiss()
    {
        // Mark any cache item which has been scheduled to be cleared as a miss.
        // We do this using callback since isHit property is private, & it will be reset on get()
        $fn = $this->isItemDeferedClearedFn;
        if ($fn($this->keyString)) {
            return true;
        }

        return parent::isMiss();
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        // We don't save cache during transaction (as cache is shared), & given the use of cache items within eZ
        // Platform kernel is not used across transaction boundaries items (these) given by pool can safely be assumed
        // to still be within transaction when save() is called.
        // ...
        // We do need to tell Pool that it should delete the item tough (which is done on commit if in transaction)
        // so this save will be done on-demand when needed.
        $clear = $this->clearFn;
        $clear($this->keyString);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $clear = $this->clearFn;
        $clear($this->keyString);

        return true;
    }
}
