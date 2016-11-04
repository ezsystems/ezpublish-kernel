<?php

/**
 * File containing the DoctrineDatabase visibility criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Visibility criterion handler.
 */
class Visibility extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\Visibility;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @todo: Needs optimisation since this subselect can potentially be problematic
     * due to large number of contentobject_id values returned. One way to fix this
     * is to use inner joins on ezcontentobject_tree table, but this is not currently
     * supported in legacy search gateway
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subSelect = $query->subSelect();

        if ($criterion->value[0] === Criterion\Visibility::VISIBLE) {
            $expression = $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_hidden', 'ezcontentobject_tree'),
                    0
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_invisible', 'ezcontentobject_tree'),
                    0
                )
            );
        } else {
            $expression = $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_hidden', 'ezcontentobject_tree'),
                    1
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_invisible', 'ezcontentobject_tree'),
                    1
                )
            );
        }

        $subSelect
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id')
            )->from(
                $this->dbHandler->quoteTable('ezcontentobject_tree')
            )->where($expression);

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
