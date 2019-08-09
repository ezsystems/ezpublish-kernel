<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Adapter;

/**
 * Interface for cache adapter which is aware of persistence transactions.
 *
 * It is used for deferring cache invalidation until transaction is committed to avoid race conditions due to
 * shared cache pool vs isolated transactions.
 *
 * @internal
 */
interface TransactionAwareAdapterInterface
{
    /**
     * Called when transaction starts.
     */
    public function beginTransaction();

    /**
     * Called when transaction is committed.
     *
     * WARNING: Must be called just AFTER database commit, to avoid theoretical cache pool race issues if done before.
     */
    public function commitTransaction();

    /**
     * Called when transaction is rolled back.
     */
    public function rollbackTransaction();
}
