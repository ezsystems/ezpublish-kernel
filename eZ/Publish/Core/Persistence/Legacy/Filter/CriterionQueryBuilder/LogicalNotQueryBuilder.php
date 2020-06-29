<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Persistence\Filter\CriterionVisitor;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
final class LogicalNotQueryBuilder implements CriterionQueryBuilder
{
    /** @var \eZ\Publish\SPI\Persistence\Filter\CriterionVisitor */
    private $criterionVisitor;

    public function __construct(CriterionVisitor $criterionVisitor)
    {
        $this->criterionVisitor = $criterionVisitor;
    }

    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof LogicalNot;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot $criterion */
        if (!$criterion->criteria[0] instanceof FilteringCriterion) {
            throw new InvalidArgumentException(
                '$criterion',
                sprintf(
                    'Criterion needs to be a Filtering Criterion, got "%s"',
                    get_class($criterion->criteria[0])
                )
            );
        }

        $constraint = $this->criterionVisitor->visitCriteria(
            $queryBuilder,
            $criterion->criteria[0]
        );

        return null !== $constraint ? sprintf('NOT (%s)', $constraint) : null;
    }
}
