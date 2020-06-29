<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Location;

use Doctrine\DBAL\ParameterType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;
use function array_map;

/**
 * @internal for internal use by Repository Filtering
 */
final class SubtreeQueryBuilder extends BaseLocationCriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof Subtree;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree $criterion */
        parent::buildQueryConstraint($queryBuilder, $criterion);

        $expressionBuilder = $queryBuilder->expr();
        $statements = array_map(
            static function (string $pathString) use ($queryBuilder, $expressionBuilder): string {
                return $expressionBuilder->like(
                    'location.path_string',
                    $queryBuilder->createNamedParameter($pathString . '%', ParameterType::STRING)
                );
            },
            $criterion->value
        );

        return (string)$expressionBuilder->orX(...$statements);
    }
}
