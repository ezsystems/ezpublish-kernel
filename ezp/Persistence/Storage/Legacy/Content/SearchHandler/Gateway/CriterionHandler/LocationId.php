<?php
/**
 * File containing the EzcDatabase location id criterion handler class
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
 * Location id criterion handler
 */
class LocationId extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\LocationId;
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
                $query->expr->in( 'node_id', $criterion->value )
            );

        return $query->expr->in( 'id', $subSelect );
    }
}

