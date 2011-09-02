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
    ezp\Base\Proxy,
    ezp\Content,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\User\GroupAbleInterface;

/**
 * This class represents a Group item
 *
 * Group is currently a facade for content objects of User Group type.
 * It requires that the User Group Content Type used has two attributes: name & description, both ezstring field types
 *
 * @property-read mixed $id
 * @property string $name
 * @property string description
 */
class Group implements GroupAbleInterface, ModelInterface, Observable
{
    /**
     * @var array Readable of properties on this object (and writable if value is true)
     * @todo Deal with translation when Content has it
     */
    protected $contentProperties = array(
        'id' => false,
        'name' => true,
        'description' => true,
    );

    /**
     * @var \ezp\Content The User Group Content Object
     */
    protected $content;

    /**
     * @var \ezp\User\Group|null The User Group locations
     */
    protected $parent;

    /**
     * @var \ezp\User\Group|null The Roles assigned to Group
     */
    protected $roles = array();

    /**
     * Creates and setups User object
     *
     * @access private Use {@link \ezp\User\Service::createGroup()} to create objects of this type
     * @param \ezp\Content $content
     * @param \ezp\User\Group $locations
     */
    public function __construct( Content $content )
    {
        $this->content = $content;
    }

    /**
     * @return \ezp\User\Group|null
     */
    public function getParent()
    {
        if ( $this->parent instanceof Proxy )
            $this->parent = $this->parent->load();

        return $this->parent;
    }

    /**
     * Roles assigned to Group
     *
     * Use {@link \ezp\User\Service::assignRole} & {@link \ezp\User\Service::unassignRole} to change
     *
     * @return \ezp\User\Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Return list of properties, where key is properties and value depends on type and is internal so should be ignored for now.
     *
     * @return array
     */
    public function properties()
    {
        return array_keys( $this->contentProperties );
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
        return $this->content->attach( $observer, $event );
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
        return $this->content->detach( $observer, $event );
    }

    /**
     * Notify listeners about certain events, by default $event is a plain 'update'
     *
     * @param string $event
     * @return Model
     */
    public function notify( $event = 'update' )
    {
        return $this->content->notify( $event );
    }

    /**
     * Magic getter
     *
     * @param string $property Property name
     * @return mixed
     * @throws PropertyNotFound If $property cannot be found
     */
    public function __get( $property )
    {
        if ( isset( $this->contentProperties[$property] ) )
        {
            if ( $property === 'id' )
                return $this->content->id;

            return $this->content->fields[$property]->value;
        }

        throw new PropertyNotFound( $property, get_class( $this ) );
    }

    /**
     * Magic setter
     *
     * @param string $property
     * @param mixed $value
     * @throws PropertyNotFound If $property cannot be found
     * @throws PropertyPermission When trying to set a value to a read-only property
     */
    public function __set( $property, $value )
    {
        if ( !isset( $this->contentProperties[$property] ) )
        {
            throw new PropertyNotFound( $property, get_class( $this ) );
        }
        else if ( $this->contentProperties[$property] )
        {
            $this->content->fields[$property] = $value;
            return;
        }

        throw new PropertyPermission( $property, PropertyPermission::WRITE, get_class( $this ) );
    }

    /**
     * Checks if a public virtual property is set
     *
     * @param string $property Property name
     * @return bool
     */
    public function __isset( $property )
    {
        return isset( $this->contentProperties[$property] )
            && ( $property === 'id' || isset( $this->content->fields[$property] ) );
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
