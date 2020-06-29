<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\SPI\Persistence\Filter\CriterionVisitor;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
final class LogicalAndQueryBuilder implements CriterionQueryBuilder
{
    /** @var \eZ\Publish\SPI\Persistence\Filter\CriterionVisitor */
    private $criterionVisitor;

    public function __construct(CriterionVisitor $criterionVisitor)
    {
        $this->criterionVisitor = $criterionVisitor;
    }

    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof LogicalAnd;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        $constraints = [];
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd $criterion */
        foreach ($criterion->criteria as $_criterion) {
            /** @var \eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion $_criterion */
            $constraint = $this->criterionVisitor->visitCriteria($queryBuilder, $_criterion);
            if (null !== $constraint) {
                $constraints[] = $constraint;
            }
        }

        if (empty($constraints)) {
            return null;
        }

        return (string)$queryBuilder->expr()->andX(...$constraints);
    }
}
