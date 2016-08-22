<?php

/**
 * File containing the CacheItem class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use Tedivm\StashBundle\Service\CacheItem as StashBundleCacheItem;

/**
 * Class CacheItem.
 *
 * Overrides set() to also call save() to behave like Stash < v0.6 did for bc.
 */
class CacheItem extends StashBundleCacheItem
{
    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        parent::set($value);
        $this->save();
    }
}
