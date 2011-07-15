<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\LogicalOr class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion;

/**
 * This class does...
 */
class LogicalOr extends LogicalOperator
{
    /**
     * Creates a new OR logic criterion.
     *
     * This criterion will match if AT LEAST ONE of the given criteria match
     *
     * @param array(Criterion) $criteria
     */
    public function __construct( array $criteria )
    {
        parent::__construct( $criteria );
    }
}
?>
