<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\User;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\IsUserBased;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
final class IsUserBasedQueryBuilder extends BaseUserCriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof IsUserBased;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\IsUserBased $criterion */
        // intentionally not using parent buildQueryConstraint
        $queryBuilder
            ->leftJoinOnce(
                'content',
                'ezuser',
                'user_storage',
                'content.id = user_storage.contentobject_id'
            );

        $isUserBased = (bool)reset($criterion->value);
        $databasePlatform = $queryBuilder->getConnection()->getDatabasePlatform();

        return $isUserBased
            ? $databasePlatform->getIsNotNullExpression('user_storage.contentobject_id')
            : $databasePlatform->getIsNullExpression('user_storage.contentobject_id');
    }
}
