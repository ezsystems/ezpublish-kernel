<?php
/**
 * File containing the EzcDatabase base field value Handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator as CriterionOperator;
use ezcQuerySelect;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use RuntimeException;

/**
 * Content locator gateway implementation using the zeta database component.
 */
abstract class Handler
{
    /**
     * DB handler to fetch additional field information
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler|\ezcDbHandler
     */
    protected $dbHandler;

    /**
     * Map of criterion operators to the respective function names in the zeta
     * Database abstraction layer.
     *
     * @var array
     */
    protected $comparatorMap = array(
        CriterionOperator::EQ => "eq",
        CriterionOperator::GT => "gt",
        CriterionOperator::GTE => "gte",
        CriterionOperator::LT => "lt",
        CriterionOperator::LTE => "lte",
        CriterionOperator::LIKE => "like",
    );

    /**
     * Transformation processor
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * Creates a new criterion handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     */
    public function __construct( EzcDbHandler $dbHandler, TransformationProcessor $transformationProcessor )
    {
        $this->dbHandler = $dbHandler;
        $this->transformationProcessor = $transformationProcessor;
    }

    /**
     * Generates query expression for operator and value of a Field Criterion.
     *
     * @throws \RuntimeException If operator is not handled.
     *
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \ezcQueryExpression
     */
    public function handle( ezcQuerySelect $query, Criterion $criterion, $column )
    {
        $column = $this->dbHandler->quoteColumn( $column );

        switch ( $criterion->operator )
        {
            case Criterion\Operator::IN:
                $filter = $query->expr->in(
                    $column,
                    array_map( array( $this, 'lowercase' ), $criterion->value )
                );
                break;

            case Criterion\Operator::BETWEEN:
                $filter = $query->expr->between(
                    $column,
                    $query->bindValue( $this->lowercase( $criterion->value[0] ) ),
                    $query->bindValue( $this->lowercase( $criterion->value[1] ) )
                );
                break;

            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
            case Criterion\Operator::LIKE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $filter = $query->expr->$operatorFunction(
                    $column,
                    $query->bindValue( $this->lowercase( $criterion->value ) )
                );
                break;

            case Criterion\Operator::CONTAINS:
                $filter = $query->expr->like(
                    $column,
                    $query->bindValue( "%" . $this->lowercase( $criterion->value ) . "%" )
                );
                break;

            default:
                throw new RuntimeException( "Unknown operator '{$criterion->operator}' for Field criterion handler." );
        }

        return $filter;
    }

    /**
     * Downcases a given string using string transformation processor.
     *
     * @param string $string
     *
     * @return string
     */
    protected function lowerCase( $string )
    {
        return $this->transformationProcessor->transformByGroup( $string, "lowercase" );
    }
}
