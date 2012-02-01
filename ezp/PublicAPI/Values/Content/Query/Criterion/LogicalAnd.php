<?php
namespace ezp\PublicAPI\Values\Content\Query\Criterion;

/**
 * This class does...
 */
class LogicalAnd extends LogicalOperator
{
    /**
     * Creates a new AND logic criterion.
     *
     * This criterion will only match if ALL of the given criteria match
     *
     * @param array(Criterion) $criteria
     */
    public function __construct( array $criteria )
    {
        parent::__construct( $criteria );
    }
}
