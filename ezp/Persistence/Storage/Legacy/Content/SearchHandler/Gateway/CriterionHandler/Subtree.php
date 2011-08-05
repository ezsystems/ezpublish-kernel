<?php
/**
 * File containing the EzcDatabase subtree criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\SearchHandler\Gateway\CriterionHandler;
use ezp\Persistence\Storage\Legacy\Content\SearchHandler\Gateway\CriterionHandler,
    ezp\Persistence\Storage\Legacy\Content\SearchHandler\Gateway\CriteriaConverter,
    ezp\Persistence\Content\Criterion;

/**
 * Subtree criterion handler
 */
class Subtree extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\Subtree;
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
        $statements = array();
        foreach ( $criterion->value as $pattern )
        {
            $statements[] = $query->expr->like( 'ezcontentobject_tree.path_string', $query->bindValue( $pattern . '%' ) );
        }

        $subSelect = $query->subSelect();
        $subSelect
            ->select( 'contentobject_id' )
            ->from( 'ezcontentobject_tree' )
            ->where(
                $query->expr->lOr( $statements )
            );

        return $query->expr->in( 'id', $subSelect );
    }
}

