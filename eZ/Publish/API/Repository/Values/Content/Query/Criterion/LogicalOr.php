<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

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
     * @param Criterion[] $criteria
     */
    public function __construct( array $criteria )
    {
        parent::__construct( $criteria );
    }
}
