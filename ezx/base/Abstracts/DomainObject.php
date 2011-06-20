<?php
/**
 * Abstract Domain object, required for generic persistent objects
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

/**
 * Domain object
 */
namespace ezx\base\Abstracts;
abstract class DomainObject implements \ezx\base\Interfaces\Serializable, \ezx\base\Interfaces\Observable, \ezx\base\Interfaces\Definition
{
    /**
     * List of event listeners
     *
     * @var \ezx\base\Interfaces\Observer[]
     */
    private $observers = array();

    /**
     * Definition of properties
     *
     * Used for serialization, __set / __get, validation (todo) and rendering (todo).
     * Could also potentially be used by storage engine to avoid a separate mapping format.
     *
     * @see\ezx\base\Interfaces\Definition::definition() For format definition
     * @var array
     */
    protected static $definition = array();

    /**
     * Attach a event listener to this subject
     *
     * @param \ezx\base\Interfaces\Observer $observer
     * @param string $event
     * @return DomainObject
     */
    public function attach( \ezx\base\Interfaces\Observer $observer, $event = 'update' )
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
     * @param \ezx\base\Interfaces\Observer $observer
     * @param string $event
     * @return DomainObject
     */
    public function detach( \ezx\base\Interfaces\Observer $observer, $event = 'update' )
    {
        if ( !empty( $this->observers[$event] ) )
        {
            foreach( $this->observers[$event] as $key => $obj )
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
     * @return DomainObject
     */
    public function notify( $event = 'update' )
    {
        if ( !empty( $this->observers[$event] ) )
        {
            foreach( $this->observers[$event] as $obj )
            {
                $obj->update( $this, $event );
            }
        }
        return $this;
    }

    /**
     * Used by var_export and other functions to init class with all values
     *
     * @static
     * @param array $properties
     * @return DomainObject
     */
    public static function __set_state( array $properties )
    {
        $class = new static();
        return $class->fromHash( $properties );
    }


    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @throws \InvalidArgumentException When trying to set invalid properties on this object
     * @param array $properties
     * @return DomainObject Return $this
     */
    public function fromHash( array $properties )
    {
        foreach ( $properties as $property => $value )
        {
            if ( !isset( static::$definition[$property] ) )
            {
                throw new \InvalidArgumentException( "'{$property}' is not a valid property on class: " . get_class( $this ) );
            }

            switch( static::$definition[$property]['type'] )
            {
                case self::TYPE_ARRAY:
                    $arrayAccess = $this->__get( $property );
                    foreach ( $value as $key => $item )
                    {
                        if ( $arrayAccess[$key] instanceof \ezx\base\Interfaces\Serializable )
                            $arrayAccess[$key]->fromHash( $item );
                        else
                            $arrayAccess[$key] = $item;
                    }
                    break;
                case self::TYPE_OBJECT:
                    $object = $this->__get( $property );
                    if ( $object instanceof \ezx\base\Interfaces\Serializable )
                        $object->fromHash( $value );
                    else
                        throw new \RuntimeException( "Property '{$property}' is of TYPE_OBJECT but does not implement \ezx\base\Interfaces\Serializable on class: " . get_class( $this ) );
                    break;
                case self::TYPE_BOOL:
                case self::TYPE_INT:
                case self::TYPE_STRING:
                case self::TYPE_FLOAT:
                    // set directly if possible to be able to set readonly properties as well
                    if ( isset( static::$definition[$property] ) && isset( $this->$property ) )
                        $this->$property = $value;
                    else
                        $this->__set( $property, $value );
                    break;
                default:
                    $type = static::$definition[$property]['type'];
                    throw new \RuntimeException( "Property '{$property}' is of unknown type: '{$type}' on class: " . get_class( $this ) );
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
        foreach( static::$definition as $property => $definition )
        {
            if ( !$internals && isset( $definition['internal'] ) )
            {
                continue;
            }

            if ( !isset( $definition['member'] ) &&
               ( $definition['type'] === self::TYPE_OBJECT || $definition['type'] === self::TYPE_ARRAY ) )
            {
                continue;
            }

            $value = $this->__get( $property );
            switch( $definition['type'] )
            {
                case self::TYPE_ARRAY:
                    $hash[$property] = array();
                    foreach ( $value as $key => $item )
                    {
                        if ( $item instanceof \ezx\base\Interfaces\Serializable )
                            $hash[$property][$key] = $item->toHash( $internals );
                        else
                            $hash[$property][$key] = $item;
                    }
                    break;
                case self::TYPE_OBJECT:
                    if ( $value instanceof \ezx\base\Interfaces\Serializable )
                        $hash[$property] = $value->toHash( $internals );
                    else
                        throw new \RuntimeException( "Property '{$property}' is of TYPE_OBJECT but does not implement \ezx\base\Interfaces\Serializable on class: " . get_class( $this ) );
                    break;
                case self::TYPE_BOOL:
                case self::TYPE_INT:
                case self::TYPE_STRING:
                case self::TYPE_FLOAT:
                    $hash[$property] = $value;
                    break;
                default:
                    throw new \RuntimeException( "Property '{$property}' is of unknown type: '{$definition['type']}' on class: " . get_class( $this ) );
            }
        }
        return $hash;
    }

    /**
     * Get value
     *
     * @throws \InvalidArgumentException
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        if ( isset( static::$definition[$name] ) )
        {
            if ( isset( static::$definition[$name]['dynamic'] ) )
            {
                $method = 'get' . ucfirst( $name );
                return $this->$method();
            }
            else if ( isset( $this->$name ) )
            {
                return $this->$name;
            }
        }
        throw new \InvalidArgumentException( "'{$name}' is not a valid property on " . get_class( $this ) );
    }

    /**
     * Set value
     * ( override to limit properties that are writable)
     *
     * @throws \InvalidArgumentException
     * @param string $name
     * @param string $value
     * @return mixed Return $value
     */
    public function __set( $name, $value )
    {
        if ( isset( static::$definition[$name] ) )
        {
            if ( isset( static::$definition[$name]['dynamic'] ) )
            {
                $method = 'set' . ucfirst( $name );
                if ( !method_exists( $this, $method ) )
                {
                    throw new \InvalidArgumentException( "'{$name}' is a readonly dynamic property on " . get_class( $this ) );
                }
                $this->$method( $value );
                return $value;
            }
            else if ( isset( $this->$name ) )
            {
                throw new \InvalidArgumentException( "'{$name}' is a readonly property on " . get_class( $this ) );
            }
        }
        throw new \InvalidArgumentException( "'{$name}' is not a valid property on " . get_class( $this ) );
    }

    /**
     * Check if property is set
     *
     * @param string $name
     * @return bool
     */
    public function __isset( $name )
    {
        if ( !isset( static::$definition[$name] ) )
            return false;

        if ( isset( static::$definition[$name]['dynamic'] ) )
            return true;

        return isset( $this->$name );
    }

    /**
     * Return definition of class
     * Final since it's the static variable that needs to be overloaded when using this abstract.
     *
     * @return array
     */
    final public static function definition()
    {
        return static::$definition;
    }
}
