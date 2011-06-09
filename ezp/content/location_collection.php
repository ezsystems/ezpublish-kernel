<?php
/**
 * File containing the ezp\Content\LocationCollection class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Content Locations collection
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class LocationCollection extends BaseCollection implements \ezp\DomainObjectInterface, \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Locations contained in current collection
     * @var array( ezp\Content\Location )
     */
    protected $locations = array();

    /**
     * Content reference for this collection
     * @var ezp\Content\Content
     */
    protected $content;

    public function byId( $locationId )
    {
        foreach ( $this->locations as $location )
        {
            if ( $location->id == (int)$locationId )
            {
                return $location;
            }
        }

        throw new \InvalidArgumentException( "Invalid location id #{$locationId} for content #{$this->content->id}" );
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
        return isset( $this->locations[$offset] );
    }

    public function offsetGet( $offset )
    {
        return $this->locations[$offset];
    }

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetSet( $offset, $value )
    {
        throw new \ezcBasePropertyPermissionException( "fieldsets", ezcBasePropertyPermissionException::READ );
    }

    /**
     * Removes location identified by $offset
     * @param integer $offset
     */
    public function offsetUnset( $offset )
    {
        unset( $this->locations[$offset] );
        // TODO : Do further operations to be inspected by repository later on in order to perform removal
    }

    public function count()
    {
        return count( $this->locations );
    }
}
?>