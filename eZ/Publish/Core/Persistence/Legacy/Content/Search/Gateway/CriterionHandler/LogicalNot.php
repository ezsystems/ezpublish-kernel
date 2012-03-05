<?php
/**
 * File containing the EzcDatabase logical not criterion handler class
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
 * Logical not criterion handler
 */
class LogicalNot extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\LogicalNot;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        return $query->expr->not(
            $converter->convertCriteria( $query, $criterion->criteria[0] )
        );
    }
}

