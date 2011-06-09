<?php
/**
 * Abstract Domain object, required for generic persistent objects
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Domain object
 */
namespace ezx\doctrine;
abstract class Abstract_Model implements Interface_Serializable, Interface_Observable, Interface_Definition
{
    /**
     * List of event listeners
     *
     * @var array(Interface_Observer)
     */
    private $observers = array();

    /**
     * Definition of properties
     *
     * Used for serialization, __set / __get, validation (todo) and rendering (todo).
     * Could also potentially be used by storage engine to avoid a separate mapping format.
     *
     * @see Interface_definition::definition() For format definition
     * @var array
     */
    protected static $definition = array();

    /**
     * Attach a event listener to this subject
     *
     * @param SplObserver $observer
     * @param string|null $event
     * @return Abstract_Model
     */
    public function attach( Interface_Observer $observer, $event = null )
    {
        if ( $event === null )
        {
            $this->observers[] = $observer;
        }
        else if ( isset( $this->observers[$event] ) )
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
     * @param SplObserver $observer
     * @param string|null $event
     * @return Abstract_Model
     */
    public function detach( Interface_Observer $observer, $event = null )
    {
        if ( $event === null )
        {
            foreach( $this->observers as $key => $obj )
            {
                if ( $obj === $observer )
                    unset( $this->observers[$key] );
            }
        }
        elseif ( !empty( $this->observers[$event] ) )
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
     * Notify listeners about certain events, if $event is null then it's plain 'update'
     *
     * @param string|null $event
     * @return Abstract_Model
     */
    public function notify( $event = null )
    {
        if ( $event === null )
        {
            foreach( $this->observers as $obj )
            {
                $obj->update( $this );
            }
        }
        elseif ( !empty( $this->observers[$event] ) )
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
     * @return Abstract_Model
     */
    public static function __set_state( array $properties )
    {
        $class = new static();
        return $class->fromHash( $properties );
    }


    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @param array $properties
     * @return Abstract_Model Content Return $this
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
                        if ( $arrayAccess[$key] instanceof Interface_Serializable )
                            $arrayAccess[$key]->fromHash( $item );
                        else
                            $arrayAccess[$key] = $item;
                    }
                    break;
                case self::TYPE_OBJECT:
                    $object = $this->__get( $property );
                    if ( $object instanceof Interface_Serializable )
                        $object->fromHash( $value );
                    else
                        throw new \RuntimeException( "Property '{$property}' is of TYPE_OBJECT but does not implement Interface_Serializable on class: " . get_class( $this ) );
                    break;
                case self::TYPE_BOOL:
                case self::TYPE_INT:
                case self::TYPE_STRING:
                case self::TYPE_FLOAT:
                    $this->__set( $property, $value );
                    break;
                default:
                    throw new \RuntimeException( "Property '{$property}' is of unknown type: '{$propertyDefinition['type']}' on class: " . get_class( $this ) );
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
                        if ( $item instanceof Interface_Serializable )
                            $hash[$property][$key] = $item->toHash( $internals );
                        else
                            $hash[$property][$key] = $item;
                    }
                    break;
                case self::TYPE_OBJECT:
                    if ( $value instanceof Interface_Serializable )
                        $hash[$property] = $value->toHash( $internals );
                    else
                        throw new \RuntimeException( "Property '{$property}' is of TYPE_OBJECT but does not implement Interface_Serializable on class: " . get_class( $this ) );
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
            if ( isset( static::$definition[$name]['readonly'] ) )
            {
                throw new \InvalidArgumentException( "'{$name}' is a readonly property on " . get_class( $this ) );
            }

            if ( isset( static::$definition[$name]['dynamic'] ) )
            {
                $method = 'set' . ucfirst( $name );
                $this->$method( $value );
                $this->notify();
                return $value;
            }
            else if ( isset( $this->$name ) )
            {
                $this->$name = $value;
                $this->notify();
                return $value;
            }
        }
        throw new \InvalidArgumentException( "'{$name}' is not a valid property on " . get_class( $this ) );
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
