<?php

/**
 * File containing the DoctrineDatabase object state id criterion handler class.
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
 * ObjectState ID criterion handler.
 */
class ObjectStateId extends CriterionHandler
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
        return $criterion instanceof Criterion\Matcher\ObjectStateId;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @todo: Needs optimisation since this subselect can potentially be problematic
     * due to large number of contentobject_id values returned. One way to fix this
     * is to use inner joins on ezcobj_state_link table, but this is not currently
     * supported in legacy search gateway
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
        $subSelect = $query->subSelect();
        /** @var Criterion\Matcher\ObjectStateId $criterion */
        $subSelect
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id')
            )->from(
                $this->dbHandler->quoteTable('ezcobj_state_link')
            )->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('contentobject_state_id'),
                    $criterion->value
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
