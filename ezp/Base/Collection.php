<?php
/**
 * File contains Collection interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use Countable,
    ArrayAccess,
    Serializable;

/**
 * Collection interface
 *
 * Note: Does not extend IteratorAggregate / Iterator to let implementers extend ArrayObject or splFixedArray
 *
 */
interface Collection extends Countable, ArrayAccess, Serializable
{
    /**
     * Returns the first index at which a given element can be found in the array, or false if it is not present.
     *
     * Uses strict comparison.
     *
     * @param mixed $item
     * @return int|string|false False if nothing was found
     */
    public function indexOf( $item );

    /**
     * Return a copy of the internal array
     *
     * @return array
     */
    public function getArrayCopy();

    /**
     * Exchange internal array with a new one, original (old) array is returned
     *
     * @param array $input
     * @return array
     */
    public function exchangeArray( $input );
}
