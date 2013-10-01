<?php
/**
 * File containing the EzcDatabase Simple field value handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
 *
 * Simple value handler is used for creating a filter on a value that makes sense to match on only as a whole.
 * Eg. timestamp, integer, boolean, relation Content id
 */
class Simple extends Handler
{
    /**
     * Generates query expression for operator and value of a Field Criterion.
     *
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \ezcQueryExpression
     */
    public function handle( ezcQuerySelect $query, Criterion $criterion, $column )
    {
        switch ( $criterion->operator )
        {
            case Criterion\Operator::CONTAINS:
                $filter = $query->expr->eq(
                    $this->dbHandler->quoteColumn( $column ),
                    $this->lowerCase( $criterion->value )
                );
                break;

            default:
                $filter = parent::handle( $query, $criterion, $column );
        }

        return $filter;
    }
}
