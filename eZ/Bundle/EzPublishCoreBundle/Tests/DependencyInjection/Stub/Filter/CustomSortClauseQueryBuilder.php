<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\Filter;

use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringSortClause;
use eZ\Publish\SPI\Repository\Values\Filter\SortClauseQueryBuilder;

/**
 * Stub for EzPublishCoreExtensionTest::testFilteringQueryBuildersAutomaticConfiguration
 * ({@see \eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\EzPublishCoreExtensionTest::testFilteringQueryBuildersAutomaticConfiguration}).
 */
class CustomSortClauseQueryBuilder implements SortClauseQueryBuilder
{
    public function accepts(FilteringSortClause $sortClause): bool
    {
        return true;
    }

    public function buildQuery(
        FilteringQueryBuilder $queryBuilder,
        FilteringSortClause $sortClause
    ): void {
        // Do nothing
    }
}
