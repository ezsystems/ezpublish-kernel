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
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission,
    Traversable;

/**
 * Abstract model class for Domain objects
 *
 * NOTE: Class is ATM meant for Domain Objects that uses a ValueObject as property backend, in other
 * words a "Persistent" Object. So instead of using this class in other cases, a base class should be
 * created that contains the functionality such objects needs, and Model should extend it.
 *
 * - Simple class:
 *
 *     class Section extends Model
 *     {
 *         protected $readWriteProperties = array(
 *             'id' => false,
 *             'identifier' => true,
 *             'name' => true,
 *         );
 *
 *         public function __construct()
 *         {
 *             $this->properties = new SectionValue();
 *         }
 *     }
 *
 *
 * - Use when setting up object based on existing value object:
 *
 *     $section = new Section();
 *     $section->setState( array( 'properties' => $valueObject ) );
 *
 */
abstract class Model implements Observable, ModelInterface
{
    /**
     * Value object that serves as the property store
     *
     * @var \ezp\Persistence\ValueObject
     */
    protected $properties;

    /**
     * Array indicates which properties are readable & writable through
     * the magic getter and setter.
     * Key is property name, value is bool indicating if property is writable.
     *
     * @todo should mention that no type checking is done internal props.?
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
        return array_keys( $this->dynamicProperties + $this->readWriteProperties );
    }

    /**
     * Attaches $observer for $event to the Model
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return \ezp\Base\Model
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
     * Detaches $observer for $event from the Model
     *
     * @param \ezp\Base\Observer $observer
     * @param string $event
     * @return \ezp\Base\Model
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
     * Notifies registered observers about $event
     *
     * @param string $event
     * @param array|null $arguments
     * @return \ezp\Base\Model
     */
    public function notify( $event = 'update', array $arguments = null )
    {
        if ( !empty( $this->observers[$event] ) )
        {
            foreach ( $this->observers[$event] as $obj )
            {
                $obj->update( $this, $event, $arguments );
            }
        }
        return $this;
    }

    /**
     * Magic getter
     *
     * @param string $property Property name
     * @return mixed
     * @throws \ezp\Base\Exception\PropertyNotFound If $property cannot be found
     */
    public function __get( $property )
    {
        if ( isset( $this->readWriteProperties[$property] ) )
        {
            if ( property_exists( $this->properties, $property ) )
            {
                return $this->properties->$property;
            }

            throw new Logic(
                '$readWriteProperties',
                "property '{$property}' could not be found on " . get_class( $this )
            );
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
     * @throws \ezp\Base\Exception\PropertyNotFound If $property cannot be found
     * @throws \ezp\Base\Exception\PropertyPermission When trying to set a value to a read-only property
     */
    public function __set( $property, $value )
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

    /**
     * Checks if a public virtual property is set
     *
     * @param string $property Property name
     * @return bool
     */
    public function __isset( $property )
    {
        return ( isset( $this->readWriteProperties[$property] ) && isset( $this->properties->$property ) )
            || ( isset( $this->dynamicProperties[$property] ) && isset( $this->$property ) );
    }

    /**
     * Sets internal variables on object from array
     *
     * Key is property name and value is property value.
     *
     * @internal
     * @param array $state
     * @return \ezp\Base\Model
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
     * @internal
     * @param string|null $property Optional, lets you specify to only return one property by name
     * @return array|mixed Array if $property is null, else value of property
     * @throws \ezp\Base\Exception\PropertyNotFound If $property is not found (when not null)
     */
    public function getState( $property = null )
    {
        // $property is provided, return its value if valid
        if ( $property !== null )
        {
            if ( !( is_string( $property ) && property_exists( $this, $property ) ) )
                throw new PropertyNotFound( $property, get_class( $this ) );

            return $this->$property;
        }

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
     * @return \ezp\Base\Model Return $this
     */
    public function fromHash( array $properties )
    {
        foreach ( $this->readWriteProperties as $property => $writable )
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
