<?php
namespace ezp\PublicAPI\Values\Content\Query\Criterion;
use ezp\PublicAPI\Values\Content\Query\Criterion,
    ezp\PublicAPI\Values\Content\Query\Criterion\Operator\Specifications,
    ezp\PublicAPI\Values\Content\Query\CriterionInterface;

/**
 * The Field Criterion class.
 *
 * Provides content filtering based on Fields contents & values.
 */
class Field extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Field Criterion.
     *
     * Matches $field against $value using $operator
     *
     * @param FieldIdentifierStruct $target The target type/field
     * @param string $operator The match operator
     * @param mixed $value The value to match against
     */
    public function __construct( $fieldIdentifer, $operator, $value )
    {
        parent::__construct( $field, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::IN, Specifications::FORMAT_ARRAY ),
            new Specifications( Operator::EQ, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::GT, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::GTE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LT, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LTE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LIKE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::BETWEEN, Specifications::FORMAT_ARRAY, null, 2 ),
        );
    }
}
