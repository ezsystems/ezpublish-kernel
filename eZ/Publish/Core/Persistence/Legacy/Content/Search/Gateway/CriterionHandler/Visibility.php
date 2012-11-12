<?php
/**
 * File containing the EzcDatabase visibility criterion handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    ezcQuerySelect;

/**
 * Visibility criterion handler
 */
class Visibility extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\Visibility;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @TODO: Needs optimisation since this subselect can potentially be problematic
     * due to large number of contentobject_id values returned. One way to fix this
     * is to use inner joins on ezcontentobject_tree table, but this is not currently
     * supported in legacy search gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();

        if ( $criterion->value[0] === Criterion\Visibility::VISIBLE )
        {
            $expression = $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'is_hidden', 'ezcontentobject_tree' ),
                    0
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'is_invisible', 'ezcontentobject_tree' ),
                    0
                )
            );
        }
        else
        {
            $expression = $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'is_hidden', 'ezcontentobject_tree' ),
                    1
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'is_invisible', 'ezcontentobject_tree' ),
                    1
                )
            );
        }

        $subSelect
            ->select(
                $this->dbHandler->quoteColumn( 'contentobject_id' )
            )->from(
                $this->dbHandler->quoteTable( 'ezcontentobject_tree' )
            )->where( $expression );

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

