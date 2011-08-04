<?php
/**
 * File containing ezp\Content\Base class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\Base\ModelInterface,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission,
    ezp\Base\Exception\InvalidArgumentType,
    Traversable;

/**
 * Abstract model class for Domain objects
 *
 * - Simple class:
 * class Section extends Model
 * {
 *     protected $readWriteProperties = array(
 *         'id' => false,
 *         'identifier' => true,
 *         'name' => true,
 *     );
 *
 *     public function __construct()
 *     {
 *         $this->properties = new SectionValue();
 *     }
 * }
 *
 *
 * - Use when setting up object based on existing value object:
 *
 * $section = Section::__set_state( array( 'properties' => $valueObject ) );
 *
 */
abstract class Model implements Observable, ModelInterface
{
    /**
     * Value object that serves as the property store
     *
     * @var object
     */
    protected $properties;

    /**
     * Array indicates which properties are readable & writable through
     * the magic getter and setter.
     * Key is property name, value is bool indicating if property is writable.
     *
     * @var array
     */
    protected $readWriteProperties;

    /**
     * Array container for virtual properties, handled dynamically by methods
     * Key is property name, value is a bool, true if member of aggregate, false if not.
     *
     * Corresponding get method name must follow pattern get<propertyName>().
     * The method will be called without any parameter
     * e.g. : for a dynamic property named "myProperty", method should be "getMyProperty()".
     *
     * If the dynamic property is writable, a set method should be defined.
     * Corresponding set method name must follow pattern set<propertyName>( $value ).
     * The method will be called with only one $value parameter.
     * e.g. : for a dynamic property named "myProperty", method should be "setMyProperty( $value )"
     *
     * @var array
     */
    protected $dynamicProperties = array();

    /**
     * List of event listeners
     *
     * @var \ezp\Base\Observer[]
     */
    private $observers = array();

    /**
     * Return list of properties, where key is properties and value depends on type and is internal so should be ignored for now.
     *
     * @return array
     */
    public function properties()
    {
        return $this->dynamicProperties + $this->readWriteProperties;
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
        if ( isset( $this->observers[$event] ) )
        {
            $this->observers[$event][] = $observer;
        }
        else
        {
            $this->observers[$event] = array( $observer );
        }
        return $this;
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
        if ( !empty( $this->observers[$event] ) )
        {
            foreach ( $this->observers[$event] as $key => $obj )
            {
                if ( $obj === $observer )
                    unset( $this->observers[$event][$key] );
            }
        }
        return $this;
    }

    /**
     * Notify listeners about certain events, by default $event is a plain 'update'
     *
     * @param string $event
     * @return Model
     */
    public function notify( $event = 'update' )
    {
        if ( !empty( $this->observers[$event] ) )
        {
            foreach ( $this->observers[$event] as $obj )
            {
                $obj->update( $this, $event );
            }
        }
        return $this;
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
        if ( isset( $this->readWriteProperties[$property] ) )
        {
            if ( property_exists( $this, $property ) )
            {
                return $this->$property;
            }
            else if ( property_exists( $this->properties, $property ) )
            {
                return $this->properties->$property;
            }
        }

        if ( isset( $this->dynamicProperties[$property] ) )
        {
            $method = "get{$property}";
            return $this->$method();
        }

        throw new PropertyNotFound( $property, get_class( $this ) );
    }

    /**
     * Magic setter
     *
     * Avoids to modify read-only properties
     *
     * @param string $property
     * @param mixed $value
     * @throws PropertyNotFound If $property cannot be found
     * @throws PropertyPermission When trying to set a value to a read-only property
     */
    public function __set( $property, $value )
    {
        if ( !isset( $this->dynamicProperties[$property] ) )
        {
            if ( !isset( $this->readWriteProperties[$property] ) )
            {
                throw new PropertyNotFound( $property, get_class( $this ) );
            }
            else if ( $this->readWriteProperties[$property] )
            {
                $this->properties->$property = $value;
                return;
            }

            throw new PropertyPermission( $property, PropertyPermission::WRITE, get_class( $this ) );
        }

        $method = "set{$property}";
        if ( method_exists( $this, $method ) )
        {
            $this->$method( $value );
        }
        else
        {
            throw new PropertyPermission( $property, PropertyPermission::WRITE, get_class( $this ) );
        }
    }

    /**
     * Checks if a public virtual property is set
     *
     * @param string $property Property name
     * @return bool
     */
    public function __isset( $property )
    {
        return isset( $this->readWriteProperties[$property] ) || isset( $this->dynamicProperties[$property] );
    }

    /**
     * Sets internal variables on object from array
     *
     * Key is property name and value is property value.
     *
     * @internal
     * @param array $state
     * @return Model
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
     * @internal
     * @return array
     */
    public function getState()
    {
        $arr = array();
        foreach ( $this as $name => $value )
        {
            $arr[$name] = $value;
        }
        return $arr;
    }

    /**
     * Set properties with hash
     *
     * @param array $properties Where key is property name and value if set is value to set on property
     * @return Model Return $this
     */
    public function fromHash( array $properties )
    {
        foreach ( $this->readWriteProperties as $property => $writable)
        {
            if ( !$writable || !isset( $properties[$property] ) )
                continue;

            $this->properties->$property = $properties[$property];
        }

        foreach ( $this->dynamicProperties as $property => $member )
        {
            if ( !$member || !isset( $properties[$property] ) )
                continue;

            $value = $this->__get( $property );
            if ( $value instanceof self )
            {
                $value->fromHash( $properties[$property] );
                continue;
            }

            if ( !$value instanceof Traversable && !is_array( $value ) )
                continue;

            foreach ( $value as $key => $item )
            {
                if ( isset( $properties[$property][$key] ) && $item instanceof self )
                    $item->fromHash( $properties[$property][$key] );
            }
        }
        return $this;
    }

    /**
     * Get properties as hash
     *
     * @param bool $includeReadOnly Include properties that are readOnly as well.
     * @return array
     */
    public function toHash( $includeReadOnly = false )
    {
        $hash = array();
        foreach ( $this->readWriteProperties as $property => $writable )
        {
            if ( !$writable && !$includeReadOnly )
                continue;

            $hash[$property] = $this->properties->$property;
        }

        foreach ( $this->dynamicProperties as $property => $member )
        {
            if ( !$member )
                continue;

            $value = $this->__get( $property );
            if ( $value instanceof self )
            {
                $hash[$property] = $value->toHash( $includeReadOnly );
                continue;
            }

            if ( !$value instanceof Traversable && !is_array( $value ) )
                continue;

            $hash[$property] = array();
            foreach ( $value as $key => $item )
            {
                if ( $item instanceof self )
                    $hash[$property][$key] = $item->toHash( $includeReadOnly );
            }
        }
        return $hash;
    }
}
