<?php
/**
 * File containing the EzcDatabase location depth criterion handler class
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
use RuntimeException;

/**
 * Location depth criterion handler
 */
class Depth extends CriterionHandler
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
        return $criterion instanceof Criterion\Depth;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $table = $this->getUniqueTableName();
        $column = $this->dbHandler->quoteColumn( 'depth', $table );

        $query->leftJoin(
            $query->alias(
                $this->dbHandler->quoteTable( 'ezcontentobject_tree' ),
                $this->dbHandler->quoteIdentifier( $table )
            ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_id', $table ),
                $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' )
            )
        );

        switch ( $criterion->operator )
        {
            case Criterion\Operator::IN:
                return $query->expr->in(
                    $column,
                    $criterion->value
                );

            case Criterion\Operator::BETWEEN:
                return $query->expr->between(
                    $column,
                    $query->bindValue( $criterion->value[0] ),
                    $query->bindValue( $criterion->value[1] )
                );

            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                return $query->expr->$operatorFunction(
                    $column,
                    $query->bindValue( reset( $criterion->value ) )
                );

            default:
                throw new RuntimeException( "Unknown operator '{$criterion->operator}' for Depth criterion handler." );
        }
    }
}

