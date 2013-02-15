<?php
/**
 * File containing the EzcDatabase object state id criterion handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use ezcQuerySelect;

/**
 * ObjectState ID criterion handler
 */
class ObjectStateId extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\ObjectStateId;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @todo: Needs optimisation since this subselect can potentially be problematic
     * due to large number of contentobject_id values returned. One way to fix this
     * is to use inner joins on ezcobj_state_link table, but this is not currently
     * supported in legacy search gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select(
                $this->dbHandler->quoteColumn( 'contentobject_id' )
            )->from(
                $this->dbHandler->quoteTable( 'ezcobj_state_link' )
            )->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                    $criterion->value
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

