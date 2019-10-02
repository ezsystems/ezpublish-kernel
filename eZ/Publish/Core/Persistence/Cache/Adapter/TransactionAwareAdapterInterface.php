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
 * It is used for deferring cache invalidation until transaction is committed.
 *
 * @internal
 */
interface TransactionAwareAdapterInterface extends TagAwareAdapterInterface
{
    /**
     * Called when transaction starts.
     */
    public function startTransaction(): void;

    /**
     * Called when transaction is either committed or rolled back.
     */
    public function stopTransaction(): void;
}
