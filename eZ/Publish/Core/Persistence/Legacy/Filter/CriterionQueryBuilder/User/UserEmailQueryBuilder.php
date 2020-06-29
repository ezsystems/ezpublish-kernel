<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\User;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserEmail;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
final class UserEmailQueryBuilder extends BaseUserCriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof UserEmail;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserEmail $criterion */
        parent::buildQueryConstraint($queryBuilder, $criterion);

        if (Operator::LIKE === $criterion->operator) {
            $expression = $queryBuilder->expr()->like(
                'user_storage.email',
                $queryBuilder->createNamedParameter(
                    $this->transformCriterionValueForLikeExpression($criterion->value)
                )
            );
        } else {
            $value = (array)$criterion->value;
            $expression = $queryBuilder->expr()->in(
                'user_storage.email',
                $queryBuilder->createNamedParameter($value, Connection::PARAM_STR_ARRAY)
            );
        }

        return $expression;
    }
}
