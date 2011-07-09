<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\LogicalAnd class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

/**
 * This class does...
 * @package ezp.persistence.content.criterion
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
?>