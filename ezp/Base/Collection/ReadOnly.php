<?php
/**
 * File contains Read Only Collection class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Collection;
use ezp\Base\Collection,
    ezp\Base\Exception\ReadOnly as ReadOnlyException,
    ezp\Base\Dumpable,
    ArrayObject,
    SplObjectStorage;

/**
 * Read Only Collection class
 *
 */
class ReadOnly extends ArrayObject implements Collection, Dumpable
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
        {
            if ( $item->id === null )
            {
                if ( $value === $item )
                    return $key;
            }
            else if ( $value->id === $item->id )
            {
                return $key;
            }
        }
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

    /**
     * Dump an object in a similar way to var_dump()
     *
     * @param int $maxDepth Maximum depth
     * @param int $currentLevel Current level
     * @param \SplObjectStorage Set of objects already printed (to avoid recursion)
     */
    public function dump( $maxDepth = Dumpable::DEFAULT_DEPTH, $currentLevel = 0, SplObjectStorage $objectSet = null )
    {
        $spaces = str_repeat( " ", 2 * $currentLevel );

        if ( $maxDepth === $currentLevel )
        {
            echo $spaces, "...\n";
            return;
        }

        if ( $objectSet === null )
        {
            $objectSet = new SplObjectStorage ();
        }
        else if ( $objectSet->contains( $this ) )
        {
            echo $spaces, "**RECURSION**\n";
            return;
        }

        $objectSet->attach( $this );

        echo
            $spaces, "object(", get_class( $this ), ") {\n",
            $spaces, "elements array(", count( $this ), "):\n";

        foreach ( $this as $key => $value )
        {
            echo $spaces, "  [$key] =>\n";
            if ( $value instanceof Dumpable  )
            {
                // Artificially increasing the currentLevel for rendering purpose
                $value->dump( $maxDepth + 1, $currentLevel + 2, $objectSet );
            }
            else if ( $value !== null )
            {
                echo get_class( $value ), "\n";
            }
            else
            {
                echo "NULL\n";
            }
        }

        $objectSet->detach( $this );

        echo $spaces, "}\n";
    }
}

?>
