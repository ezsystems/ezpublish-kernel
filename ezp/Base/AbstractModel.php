<?php
/**
 * File containing ezp\Content\Base class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base;

/**
 * Abstract model class for Domain objects
 *
 * @package ezp
 * @subpackage base
 */
abstract class AbstractModel implements Interfaces\Observable, Interfaces\Model
{
    /**
     * Array indicates which public/protected properties are readable through
     * the magic getter (__get)
     * Key is property name, value is bool or a string to indicate mapping in case of FieldTypes
     * If value is !!true that indicates that property is included in serialization.
     *
     * @var array
     */
    protected $readableProperties;

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
     * @var Interfaces\Observer[]
     */
    private $observers = array();

    /**
     * Return list of properties, where key is properties and value definition.
     *
     * @return array
     */
    public function properties()
    {
        return $this->dynamicProperties + $this->readableProperties;
    }

    /**
     * Attach a event listener to this subject
     *
     * @param Interfaces\Observer $observer
     * @param string $event
     * @return AbstractModel
     */
    public function attach( Interfaces\Observer $observer, $event = 'update' )
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
     * @param Interfaces\Observer $observer
     * @param string $event
     * @return AbstractModel
     */
    public function detach( Interfaces\Observer $observer, $event = 'update' )
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
     * @return AbstractModel
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
     * @throws Exception\PropertyNotFound If $property cannot be found
     */
    public function __get( $property )
    {
        if ( isset( $this->readableProperties[$property] ) )
        {
            return $this->$property;
        }

        if ( isset( $this->dynamicProperties[$property] ) )
        {
            $method = "get{$property}";
            return $this->$method();
        }

        throw new Exception\PropertyNotFound( $property, get_class( $this ) );
    }

    /**
     * Magic setter
     *
     * Avoids to modify read-only properties
     *
     * @param string $property
     * @param mixed $value
     * @throws Exception\PropertyNotFound If $property cannot be found
     * @throws Exception\PropertyPermission When trying to set a value to a read-only property
     */
    public function __set( $property, $value )
    {
        if ( !isset( $this->dynamicProperties[$property] ) )
        {
            if ( !isset( $this->readableProperties[$property] ) )
            {
                throw new Exception\PropertyNotFound( $property, get_class( $this ) );
            }

            throw new Exception\PropertyPermission( $property, Exception\PropertyPermission::WRITE, get_class( $this ) );
        }

        $method = "set{$property}";
        if ( method_exists( $this, $method ) )
        {
            $this->$method( $value );
        }
        else
        {
            throw new Exception\PropertyPermission( $property, Exception\PropertyPermission::WRITE, get_class( $this ) );
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
        return isset( $this->readableProperties[$property] ) || isset( $this->dynamicProperties[$property] );
    }

    /**
     * Restores the state of a content object, gives access to initialize an object on properties that are not public.
     *
     * This function furthermore does not perform any type checking so is purely for internal use.
     *
     * @internal
     * @param array $state
     * @return AbstractModel
     */
    public static function __set_state( array $state )
    {
        $obj = new static();
        foreach ( $state as $property => $value )
        {
            if (
                isset( $obj->readableProperties[$property] ) ||
                isset( $obj->dynamicProperties[$property] ) && property_exists( $obj, $property )
            )
            {
                $obj->$property = $value;
            }
        }

        return $obj;
    }

    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @param array $properties
     * @return AbstractModel Return $this
     */
    public function fromHash( array $properties )
    {
        foreach ( $this->readableProperties as $property => $member )
        {
            if ( !$member || !isset( $properties[$property] ) )
                continue;

            $this->$property = $properties[$property];
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

            if ( !$value instanceof \Traversable && !is_array( $value ) )
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
     * Get properties with hash, name is same as used in ezc Persistent
     *
     * @param bool $internals Include internal data like id and version in hash if true
     * @return array
     */
    public function toHash( $internals = false )
    {
        $hash = array();
        foreach ( $this->readableProperties as $property => $member )
        {
            if ( !$member && !$internals )
                continue;

            $hash[$property] = $this->$property;
        }

        foreach ( $this->dynamicProperties as $property => $member )
        {
            if ( !$member )
                continue;

            $value = $this->__get( $property );
            if ( $value instanceof self )
            {
                $hash[$property] = $value->toHash( $internals );
                continue;
            }

            if ( !$value instanceof \Traversable && !is_array( $value ) )
                continue;

            $hash[$property] = array();
            foreach ( $value as $key => $item )
            {
                if ( $item instanceof self )
                    $hash[$property][$key] = $item->toHash( $internals );
            }
        }
        return $hash;
    }
}

?>
