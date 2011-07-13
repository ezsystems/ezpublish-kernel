<?php
/**
 * File containing the ezp\Persistence\Content\Interfaces\Criterion interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Interfaces;

/**
 * Base interface for Criterion implementations
 */
interface Criterion
{
    /**
     * Constructs a Criterion for $target with operator $operator on $value
     *
     * @param string $target The target (field identifier for a field, metadata identifier, etc)
     * @param string $operator The criterion operator, from Criterion\Operator
     * @param mixed $value The Criterion value, either as an individual item or an array
     */
    public function __construct( $target, $operator, $value );
}
?>
