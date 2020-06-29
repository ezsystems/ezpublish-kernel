<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Filter;

use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering.
 * Visits instances of {@see \eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder}.
 */
interface CriterionVisitor
{
    public function visitCriteria(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): string;
}
