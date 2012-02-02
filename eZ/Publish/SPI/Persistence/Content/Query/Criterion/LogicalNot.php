<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Query\Criterion\LogicalNot class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Query\Criterion;

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
    public function __construct( Criterion $criterion )
    {
        parent::__construct( array( $criterion ) );
    }
}
?>
