<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\User\Metadata;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
final class GroupQueryBuilder implements CriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof UserMetadata && $criterion->target === UserMetadata::GROUP;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata $criterion */
        $value = (array)$criterion->value;

        $queryBuilder
            ->joinOnce(
                'content',
                LocationGateway::CONTENT_TREE_TABLE,
                'user_location',
                'content.owner_id = user_location.contentobject_id'
            )
            ->joinOnce(
                'user_location',
                LocationGateway::CONTENT_TREE_TABLE,
                'user_group_location',
                'user_location.parent_node_id = user_group_location.node_id'
            );

        return $queryBuilder->expr()->in(
            'user_group_location.contentobject_id',
            $queryBuilder->createNamedParameter($value, Connection::PARAM_INT_ARRAY)
        );
    }
}
