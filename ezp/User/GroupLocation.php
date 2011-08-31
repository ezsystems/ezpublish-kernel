<?php
/**
 * File containing Group object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Configuration,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission,
    ezp\Base\ModelInterface,
    ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Content\Location,
    ezp\User\Group;

/**
 * This class represents a Group item
 *
 * Group is currently a facade for content objects of User Group type.
 * It requires that the User Group Content Type used has two attributes: name & description, both ezstring field types
 *
 * @property-read mixed $id
 * @property string $name
 * @property string description
 * @property-read \ezp\User\Role[] $roles Use {@link \ezp\User\Service::assignRole} & {@link \ezp\User\Service::unassignRole}
 */
class GroupLocation implements ModelInterface, Observable
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
     * @var \ezp\User\Group The Group assigned to this location
     */
    protected $group;

    /**
     * Creates and setups User object
     *
     * @access private Use {@link \ezp\User\Service::createGroup()} to create objects of this type
     */
    public function __construct( Location $location, Group $group = null )
    {
        $this->location = $location;
        $this->group = $group;
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
     * Get group assigned to this location
     *
     * @return \ezp\User\Group
     */
    public function getGroup()
    {
        if ( $this->group !== null )
            return $this->group;

        return $this->group = new Group( $this->location->content );
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
