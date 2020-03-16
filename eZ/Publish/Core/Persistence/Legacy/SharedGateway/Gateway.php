<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\SharedGateway;

/**
 * Database platform-dependent shared Gateway.
 *
 * @internal For internal use by Legacy Storage Gateways.
 */
interface Gateway
{
    /**
     * Get the next value for auto-incremented column.
     *
     * For the most database platforms it should be NULL to indicate that the storage engine
     * for the given platform should make a decision.
     * For some edge cases however, when the given platform has known issues, the value can be
     * pre-determined.
     *
     * Note that it's the responsibility of a caller to pass a sequence name associated with the
     * passed column.
     */
    public function getColumnNextIntegerValue(
        string $tableName,
        string $columnName,
        string $sequenceName
    ): ?int;

    /**
     * Get the most recently inserted id for the given sequence name.
     *
     * Note that sequence names are ignored by database drivers not supporting sequences, so the
     * sequence name can be passed as a constant, regardless of the underlying database connection.
     *
     * It returns integer as all the IDs in the eZ Platform Legacy Storage are (big)integers
     */
    public function getLastInsertedId(string $sequenceName): int;
}
