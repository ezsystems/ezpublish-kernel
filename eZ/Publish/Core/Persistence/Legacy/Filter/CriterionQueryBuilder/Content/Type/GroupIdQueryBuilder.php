<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeGroupId;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * Content Type Group ID Criterion visitor query builder.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeGroupId
 *
 * @internal for internal use by Repository Filtering
 */
final class GroupIdQueryBuilder extends BaseQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof ContentTypeGroupId;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeGroupId $criterion */
        $queryBuilder
            ->joinOnce(
                'content',
                ContentTypeGateway::CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE,
                'content_type_group_assignment',
                'content.contentclass_id = content_type_group_assignment.contentclass_id'
            );

        $queryBuilder
            ->joinOnce(
                'content_type_group_assignment',
                ContentTypeGateway::CONTENT_TYPE_GROUP_TABLE,
                'content_type_group',
                'content_type_group_assignment.group_id = content_type_group.id'
            );

        return $queryBuilder->expr()->in(
            'content_type_group.id',
            $queryBuilder->createNamedParameter(
                $criterion->value,
                Connection::PARAM_INT_ARRAY
            )
        );
    }
}
