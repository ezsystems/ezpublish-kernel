<?php
/**
 * File containing the ezp\Content\CriterionFactory class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Persistence\Content\Criterion;

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
        $this->criterionClass = $criterionClass;
    }

    public function eq( $target, $value )
    {
        return $this->handleCriterion( $target, Criterion\Operator::EQ, $value );
    }

    public function gt( $target, $value )
    {
        return $this->handleCriterion( $target, Criterion\Operator::GT, $value );
    }

    public function gte( $target, $value )
    {
        return $this->handleCriterion( $target, Criterion\Operator::GTE, $value );
    }

    public function lt( $target, $value )
    {
        return $this->handleCriterion( $target, Criterion\Operator::LT, $value );
    }

    public function lte( $target, $value )
    {
        return $this->handleCriterion( $target, Criterion\Operator::LTE, $value );
    }

    public function in( $target, $value )
    {
        return $this->handleCriterion( $target, Criterion\Operator::IN, $value );
    }

    /**
     * Like criterion
     * @param mixed
     * @param string $target
     */
    public function like( $target, $value )
    {
        return $this->handleCriterion( $target, Criterion\Operator::LIKE, $value );
    }

    /**
     * Adds the Criterion that checks if the value is between $valueOne and $valueTwo
     *
     * @param string $target
     * @param mixed $valueOne range start value
     * @param mixed $valieTwo range end value
     */
    public function between( $target, $valueOne, $valueTwo )
    {
        return $this->handleCriterion( $target, Criterion\Operator::IN, array( $valueOne, $valueTwo) );
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
     * The QueryBuilder object
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * The Criterion class we are building.
     * @var string
     */
    private $criterionClass;
}
?>