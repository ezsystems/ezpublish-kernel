<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection;

/**
 * "Enum" for the Symfony Service tag names provided by the Extension.
 */
class ServiceTags
{
    /**
     * Auto-configured tag name for
     * {@see \eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder}.
     */
    public const FILTERING_CRITERION_QUERY_BUILDER = 'ezplatform.filter.criterion.query_builder';

    /**
     * Auto-configured tag name for
     * {@see \eZ\Publish\SPI\Repository\Values\Filter\SortClauseQueryBuilder}.
     */
    public const FILTERING_SORT_CLAUSE_QUERY_BUILDER = 'ezplatform.filter.sort_clause.query_builder';
}
