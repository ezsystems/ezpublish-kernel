<?php
/**
 * File containing ezp\Content\BaseCollection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content
 */

namespace ezp\Content;

abstract class BaseCollection extends Base implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $properties = array( 'objects' => array() );

    public function byId( $id )
    {
        foreach ( $this->properties['objects'] as $object )
        {
            if ( $object->id == (int)$id )
            {
                return $object;
            }
        }

        throw new \InvalidArgumentException( "Invalid object id #{$id}" );
    }

    /**
     * Returns the iterator for this object
     * @return Iterator
     */
    public function getIterator()
    {
        // TODO : Use a dedicated iterator
        return new \ArrayIterator( $this );
    }

    public function offsetExists( $offset )
    {
        return isset( $this->properties['objects'][$offset] );
    }

    public function offsetGet( $offset )
    {
        return $this->properties['objects'][$offset];
    }

    public function offsetSet( $offset, $value )
    {
        $this->properties['objects'][$offset] = $value;
    }

    /**
     * Removes location identified by $offset
     * @param integer $offset
     */
    public function offsetUnset( $offset )
    {
        unset( $this->properties['objects'][$offset] );
    }

    public function count()
    {
        return count( $this->properties['objects'] );
    }
}

?>
