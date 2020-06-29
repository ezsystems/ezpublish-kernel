<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\User;

use Doctrine\DBAL\ParameterType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\IsUserEnabled;
use eZ\Publish\Core\FieldType\User\UserStorage\Gateway\DoctrineStorage;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
final class IsUserEnabledQueryBuilder extends BaseUserCriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof IsUserEnabled;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\IsUserEnabled $criterion */
        parent::buildQueryConstraint($queryBuilder, $criterion);

        $queryBuilder->joinOnce(
            'user_storage',
            DoctrineStorage::USER_SETTING_TABLE,
            'user_settings',
            'user_storage.contentobject_id = user_settings.user_id'
        );

        return $queryBuilder->expr()->eq(
            'user_settings.is_enabled',
            $queryBuilder->createNamedParameter(
                (int)reset($criterion->value),
                ParameterType::INTEGER
            )
        );
    }
}
