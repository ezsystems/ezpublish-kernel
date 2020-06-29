<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\SPI\Persistence\Filter\CriterionVisitor as FilteringCriterionVisitor;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;
use function sprintf;

/**
 * @internal Type-hint {@see \eZ\Publish\SPI\Persistence\Filter\CriterionVisitor} instead
 */
final class CriterionVisitor implements FilteringCriterionVisitor
{
    /** @var \eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder[] */
    private $criterionQueryBuilders;

    public function __construct(iterable $criterionQueryBuilders)
    {
        $this->setCriterionQueryBuilders($criterionQueryBuilders);
    }

    public function setCriterionQueryBuilders(iterable $criterionQueryBuilders): void
    {
        $this->criterionQueryBuilders = $criterionQueryBuilders;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException if there's no builder for a criterion
     */
    public function visitCriteria(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): string {
        foreach ($this->criterionQueryBuilders as $criterionQueryBuilder) {
            if ($criterionQueryBuilder->accepts($criterion)) {
                return $criterionQueryBuilder->buildQueryConstraint(
                    $queryBuilder,
                    $criterion
                );
            }
        }

        throw new NotImplementedException(
            sprintf(
                'There is no Filtering Criterion Query Builder for %s Criterion',
                get_class($criterion)
            )
        );
    }
}
