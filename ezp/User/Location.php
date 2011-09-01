<?php
/**
 * File contains Abstract Location
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Exception\PropertyNotFound,
    ezp\Base\ModelInterface,
    ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Content\Location as ContentLocation;

/**
 * User/Group Abstract Location
 *
 * A interface for classes that represent locations ( user & user group )
 */
abstract class Location implements Observable, ModelInterface
{
    /**
     * @var \ezp\Content\Location The User Group Content Object
     */
    protected $location;

    /**
     * @var \ezp\User\GroupLocation The parent GroupLocation of this location
     */
    protected $parent;

    /**
     * Creates and setups User object
     *
     * @access private Use {@link \ezp\User\Service::createGroup()} to create objects of this type
     */
    public function __construct( ContentLocation $location )
    {
        $this->location = $location;
    }

    /**
     * Get parent location of this location
     *
     * @return \ezp\User\GroupLocation
     */
    public function getParent()
    {
        if ( $this->parent !== null )
            return $this->parent;

        return $this->parent = new GroupLocation( $this->location->parent );
    }

    /**
     * Attach a event listener to this subject
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return Model
     */
    public function attach( Observer $observer, $event = 'update' )
    {
        return $this->location->attach( $observer, $event );
    }

    /**
     * Detach a event listener to this subject
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return Model
     */
    public function detach( Observer $observer, $event = 'update' )
    {
        return $this->location->detach( $observer, $event );
    }

    /**
     * Notify listeners about certain events, by default $event is a plain 'update'
     *
     * @param string $event
     * @return Model
     */
    public function notify( $event = 'update' )
    {
        return $this->location->notify( $event );
    }

    /**
     * Sets internal variables on object from array
     *
     * Key is property name and value is property value.
     *
     * @access private
     * @param array $state
     * @return Model
     * @throws \ezp\Base\Exception\PropertyNotFound If one of the properties in $state is not found
     */
    public function setState( array $state )
    {
        foreach ( $state as $name => $value )
        {
            if ( property_exists( $this, $name ) )
                $this->$name = $value;
            else
                throw new PropertyNotFound( $name, get_class( $this ) );
        }
        return $this;
    }

    /**
     * Gets internal variables on object as array
     *
     * Key is property name and value is property value.
     *
     * @access private
     * @param string|null $property Optional, lets you specify to only return one property by name
     * @return array|mixed Always returns array if $property is null, else value of property
     * @throws \ezp\Base\Exception\PropertyNotFound If $property is not found (when not null)
     */
    public function getState( $property = null )
    {
        $arr = array();
        foreach ( $this as $name => $value )
        {
            if ( $property === $name )
                return $value;
            else if ( $property === null )
                $arr[$name] = $value;
        }

        if ( $property !== null )
            throw new PropertyNotFound( $property, get_class( $this ) );

        return $arr;
    }
}
