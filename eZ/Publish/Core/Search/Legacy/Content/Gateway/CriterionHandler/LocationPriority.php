<?php
/**
 * File containing the DoctrineDatabase location priority criterion handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Location priority criterion handler
 */
class LocationPriority extends CriterionHandler
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
        return $criterion instanceof Criterion\LocationPriority;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();

        switch ( $criterion->operator )
        {
            case Criterion\Operator::BETWEEN:
                $subSelect
                    ->select(
                        $this->dbHandler->quoteColumn( 'contentobject_id' )
                    )->from(
                        $this->dbHandler->quoteTable( 'ezcontentobject_tree' )
                    )->where(
                        $query->expr->between(
                            $this->dbHandler->quoteColumn( 'priority' ),
                            $criterion->value[0],
                            $criterion->value[1]
                        )
                    );
                break;

            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $subSelect
                    ->select(
                        $this->dbHandler->quoteColumn( 'contentobject_id' )
                    )->from(
                        $this->dbHandler->quoteTable( 'ezcontentobject_tree' )
                    )->where(
                        $query->expr->$operatorFunction(
                            $this->dbHandler->quoteColumn( 'priority' ),
                            reset( $criterion->value )
                        )
                    );
        }

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

