<?php
/**
 * File containing the EzcDatabase parent location id criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\Locator\Gateway\CriterionHandler;
use ezp\Persistence\LegacyStorage\Content\Locator\Gateway\CriterionHandler,
    ezp\Persistence\LegacyStorage\Content\Locator\Gateway\CriteriaConverter,
    ezp\Persistence\Content\Criterion;

/**
 * Parent location id criterion handler
 */
class ParentLocationId extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\ParentLocationId;
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
        $subSelect
            ->select( 'contentobject_id' )
            ->from( 'ezcontentobject_tree' )
            ->where(
                $query->expr->in( 'parent_node_id', $criterion->value )
            );

        return $query->expr->in( 'id', $subSelect );
    }
}

