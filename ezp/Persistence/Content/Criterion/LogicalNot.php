<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\LogicalNot class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion;

/**
 * A NOT logical criterion
 *
 */
class LogicalNot extends LogicalOperator
{
    /**
     * Creates a new NOT logic criterion.
     *
     * Will match of the given criterion doesn't match
     *
     * @param array(Criterion) $criteria One criterion, as a an array
     *
     * @throws InvalidArgumentException if more than one criterion is given in the array parameter
     */
    public function __construct( array $criterion )
    {
        if ( count( $criterion ) > 1 )
        {
            throw new \InvalidArgumentException( "This operator only accepts one Criterion" );
        }

        parent::__construct( $criterion );
    }
}
?>
