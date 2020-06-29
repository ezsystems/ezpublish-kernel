<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\Filter;

use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * Stub for EzPublishCoreExtensionTest::testFilteringQueryBuildersAutomaticConfiguration
 * ({@see \eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\EzPublishCoreExtensionTest::testFilteringQueryBuildersAutomaticConfiguration}).
 */
class CustomCriterionQueryBuilder implements CriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return true;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        return null;
    }
}
