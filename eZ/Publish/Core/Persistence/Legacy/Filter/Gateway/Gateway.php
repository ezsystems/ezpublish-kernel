<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\Gateway;

use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * Repository filtering gateway.
 *
 * @internal for internal use by Legacy Storage
 */
interface Gateway
{
    /**
     * Return number of matched rows for the given Criteria (a total count w/o pagination constraints).
     */
    public function count(FilteringCriterion $criterion): int;

    /**
     * Return iterator for raw Repository data for the given Query result filtered by the given Criteria,
     * sorted by the given Sort Clauses and constrained by the given pagination limit & offset.
     *
     * @param \eZ\Publish\SPI\Repository\Values\Filter\FilteringSortClause[] $sortClauses
     */
    public function find(
        FilteringCriterion $criterion,
        array $sortClauses,
        int $limit,
        int $offset
    ): iterable;
}
