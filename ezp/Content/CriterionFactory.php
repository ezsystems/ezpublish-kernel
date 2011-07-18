<?php
/**
 * File containing the ezp\Content\CriterionFactory class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\Operator,
    ezp\Persistence\Content\Criterion\LogicalOr,
    ezp\Persistence\Content\Criterion\LogicalAnd,
    ezp\Persistence\Content\Criterion\LogicalNot,
    InvalidArgumentException;

/**
 * This class provides a factory interface to a criterion object.
 *
 * <code>
 * $queryBuilder = new QueryBuilder;
 * $criterionFactory = $queryBuilder->metadata;
 * </code>
 */

class CriterionFactory
{
    /**
     * Constructs a criterion factory for $criterion
     *
     * @param string $criterionClass The Criterion we are creating a factory for
     */
    public function __construct( $criterionClass )
    {
        if ( !class_exists( $criterionClass ) )
        {
            throw new InvalidArgumentException( "Criterion class $criterionClass couldn't be found" );
        }
        $this->criterionClass = $criterionClass;
    }

    /**
     * Equal operator
     */
    public function eq( $target, $value )
    {
        return $this->handleCriterion( $target, Operator::EQ, $value );
    }

    /**
     * Greater than operator
     */
    public function gt( $target, $value )
    {
        return $this->handleCriterion( $target, Operator::GT, $value );
    }

    /**
     * Greater than or equals operator
     */
    public function gte( $target, $value )
    {
        return $this->handleCriterion( $target, Operator::GTE, $value );
    }

    /**
     * Lower than operator
     */
    public function lt( $target, $value )
    {
        return $this->handleCriterion( $target, Operator::LT, $value );
    }

    /**
     * Lower than or equals operator
     */
    public function lte( $target, $value )
    {
        return $this->handleCriterion( $target, Operator::LTE, $value );
    }

    /**
     * In operator
     */
    public function in( $target, $value )
    {
        return $this->handleCriterion( $target, Operator::IN, $value );
    }

    /**
     * Like operator
     * @param mixed
     * @param string $target
     */
    public function like( $target, $value )
    {
        return $this->handleCriterion( $target, Operator::LIKE, $value );
    }

    /**
     * Between range operator
     *
     * @param string $target
     * @param mixed $valueOne range start value
     * @param mixed $valieTwo range end value
     */
    public function between( $target, $valueOne, $valueTwo )
    {
        return $this->handleCriterion( $target, Operator::IN, array( $valueOne, $valueTwo) );
    }

    /**
     * Logical or between 2...n criteria
     *
     * @param Criterion $criterionOne
     * @param Criterion $criterionTwo$...
     */
    public function logicalOr( Criterion $criterionOne, Criterion $criterionTwo )
    {
        return new LogicalOr( func_get_args() );
    }

    /**
     * Logical and between 2...n criteria
     *
     * @param Criterion $criterionOne
     * @param Criterion $criterionTwo$...
     */
    public function logicalAnd( Criterion $criterionOne, Criterion $criterionTwo )
    {
        return new LogicalAnd( func_get_args() );
    }

    /**
     * Logical not on one Criterion
     *
     * @param Criterion $criterion
     */
    public function logicalNot( Criterion $criterion )
    {
        return new LogicalNot( array( $criterion ) );
    }

    /**
     * Handles factory of the current criterion with a given operator & value
     *
     * @param string $operator
     * @param $argument$... Criterion arguments
     *
     * @return QueryBuilder
     */
    private function handleCriterion( $target, $operator, $value )
    {
        return new $this->criterionClass( $target, $operator, $value );
    }

    /**
     * The Criterion class we are building.
     * @var string
     */
    private $criterionClass;
}
?>
