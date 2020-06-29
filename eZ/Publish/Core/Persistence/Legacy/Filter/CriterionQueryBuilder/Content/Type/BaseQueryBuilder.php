<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * Content Type Criterion visitor query builder base.
 *
 * @internal for internal use by Repository Filtering
 */
abstract class BaseQueryBuilder implements CriterionQueryBuilder
{
    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        $queryBuilder
            ->joinOnce(
                'content',
                ContentTypeGateway::CONTENT_TYPE_TABLE,
                'content_type',
                'content.contentclass_id = content_type.id AND content_type.version = 0'
            );

        // the returned query constraint depends on concrete implementations
        return null;
    }
}
