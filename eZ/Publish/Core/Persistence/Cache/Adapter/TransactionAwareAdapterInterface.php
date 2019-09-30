<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Adapter;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Interface for cache adapter which is aware of persistence transactions.
 *
 * It is used for deferring cache invalidation until transaction is committed to avoid race conditions due to
 * shared cache pool vs isolated transactions.
 *
 * @internal
 */
interface TransactionAwareAdapterInterface extends TagAwareAdapterInterface
{
    /**
     * Called when transaction starts.
     */
    public function beginTransaction(): void;

    /**
     * Called when transaction is committed.
     *
     * WARNING: Must be called just AFTER database commit, to avoid theoretical cache pool race issues if done before.
     */
    public function commitTransaction(): void;

    /**
     * Called when transaction is rolled back.
     */
    public function rollbackTransaction(): void;
}
