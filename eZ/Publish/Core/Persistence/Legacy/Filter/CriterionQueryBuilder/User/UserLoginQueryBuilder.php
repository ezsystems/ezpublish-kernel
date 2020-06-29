<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\User;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserLogin;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
final class UserLoginQueryBuilder extends BaseUserCriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof UserLogin;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserId $criterion */
        parent::buildQueryConstraint($queryBuilder, $criterion);

        $expr = $queryBuilder->expr();
        if (Operator::LIKE === $criterion->operator) {
            return $expr->like(
                'user_storage.login',
                $queryBuilder->createNamedParameter(
                    $this->transformCriterionValueForLikeExpression($criterion->value)
                )
            );
        }

        $value = (array)$criterion->value;

        return $expr->in(
            'user_storage.login',
            $queryBuilder->createNamedParameter($value, Connection::PARAM_STR_ARRAY)
        );
    }
}
