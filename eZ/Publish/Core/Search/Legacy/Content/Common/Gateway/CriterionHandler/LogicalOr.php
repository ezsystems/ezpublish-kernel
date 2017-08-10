<?php

/**
 * File containing the DoctrineDatabase logical or criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Logical or criterion handler.
 */
class LogicalOr extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriterionInterface $criterion
     *
     * @return bool
     */
    public function accept(CriterionInterface $criterion)
    {
        return $criterion instanceof Criterion\LogicalOperator\LogicalOr;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param CriterionInterface $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        CriterionInterface $criterion,
        array $languageSettings
    ) {
        $subexpressions = array();
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator\LogicalOr $criterion */
        foreach ($criterion->criteria as $subCriterion) {
            $subexpressions[] = $converter->convertCriteria(
                $query,
                $subCriterion,
                $languageSettings
            );
        }

        return $query->expr->lOr($subexpressions);
    }
}
