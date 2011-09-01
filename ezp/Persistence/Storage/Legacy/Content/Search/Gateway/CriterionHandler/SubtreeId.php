<?php
/**
 * File containing the EzcDatabase subtreeId criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriterionHandler;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriterionHandler,
    ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriteriaConverter,
    ezp\Persistence\Content\Criterion;

/**
 * Subtree criterion handler
 */
class SubtreeId extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\SubtreeId;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, \ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();

        $statements = array();
        foreach ( $criterion->value as $pattern )
        {
            $statements[] = $subSelect->expr->like(
                $this->dbHandler->quoteColumn( 'path_string', 'ezcontentobject_tree' ),
                $subSelect->bindValue( $pattern . '%' )
            );
        }

        $subSelect
            ->select(
                $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_tree' )
            )->from(
                $this->dbHandler->quoteTable( 'ezcontentobject_tree' )
            )->where(
                $query->expr->lOr( $statements )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

