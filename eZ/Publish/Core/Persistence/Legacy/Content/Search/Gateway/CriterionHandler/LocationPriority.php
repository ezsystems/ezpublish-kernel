<?php

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use ezcQuerySelect;

class LocationPriority extends CriterionHandler
{

    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\LocationPriority;
    }

    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $column = $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' );

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

