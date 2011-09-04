<?php
/**
 * File contains Read Only Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Collection;
use ezp\Base\Collection,
    ezp\Base\Exception\ReadOnly as ReadOnlyException,
    ArrayObject;

/**
 * Read Only Collection class
 *
 */
class ReadOnly extends ArrayObject implements Collection
{
    /**
     * Returns the first index at which a given element can be found in the array, or false if it is not present.
     *
     * Uses strict comparison.
     *
     * @param mixed $item
     * @return int|string|false False if nothing was found
     */
    public function indexOf( $item )
    {
        foreach ( $this as $key => $value )
            if ( $value === $item )
                return $key;
        return false;
    }

    /**
     * Overloads offsetSet() to do exception about being read only.
     *
     * @internal
     * @throws ezp\Base\Exception\ReadOnly
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        throw new ReadOnlyException( 'Collection' );
    }

    /**
     * Overloads offsetUnset() to do exception about being read only.
     *
     * @internal
     * @throws ezp\Base\Exception\ReadOnly
     * @param string|int $offset
     */
    public function offsetUnset( $offset )
    {
        throw new ReadOnlyException( 'Collection' );
    }

    /**
     * Overloads exchangeArray() to do exception about being read only.
     *
     * @throws ezp\Base\Exception\ReadOnly
     * @param array $input
     * @return array
     */
    public function exchangeArray( $input )
    {
        throw new ReadOnlyException( 'Collection' );
    }
}

?>
