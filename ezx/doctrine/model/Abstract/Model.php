<?php
/**
 * Abstract Content Field decorator (datatype) object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 *
 */
namespace ezx\doctrine\model;
abstract class Abstract_Model implements Interface_Serializable, Interface_Observable
{
    /**
     * List of event listeners
     *
     * @var array(Interface_Observer)
     */
    private $_observers = array();

    /**
     * List of readonly properties
     *
     * @var array(string)
     */
    private $_readonly = array();

    /**
     * List of aggregate members, only applies to objects that implements Interface_Serializable
     * Used for figuring out what to serialize as part of this object!
     *
     * @var array(string)
     */
    protected $_aggregateMembers = array();

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
            $this->_observers[] = $observer;
        else if ( isset( $this->observers[$event] ) )
            $this->_observers[$event][] = $observer;
        else
            $this->_observers[$event] = array( $observer );
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
            foreach( $this->_observers as $key => $obj )
            {
                if ( $obj === $observer )
                    unset( $this->_observers[$key] );
            }
        }
        elseif ( !empty( $this->_observers[$event] ) )
        {
            foreach( $this->_observers[$event] as $key => $obj )
            {
                if ( $obj === $observer )
                    unset( $this->_observers[$event][$key] );
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
            foreach( $this->_observers as $obj )
            {
                $obj->update( $this );
            }
        }
        elseif ( !empty( $this->_observers[$event] ) )
        {
            foreach( $this->_observers[$event] as $obj )
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
        return $class->setState( $properties );
    }


    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @param array $properties
     * @return Abstract_Model Content Return $this
     */
    public function setState( array $properties )
    {
        foreach ( $properties as $property => $value )
        {
            if ( !$value instanceof Interface_Serializable && $this->$property instanceof Interface_Serializable )
                $this->$property->setState( $value );
            elseif ( $this->$property instanceof \ArrayAccess )
            {
                $arrayAccess = $this->$property;
                foreach ( $value as $key => $item )
                {
                    if ( $arrayAccess[$key] instanceof Interface_Serializable )
                        $arrayAccess[$key]->setState( $item );
                    else
                        $arrayAccess[$key] = $item;
                }
            }
            else
                $this->$property = $value;
        }
        return $this;
    }

    /**
     * Get properties with hash, name is same as used in ezc Persistent
     *
     * @return array
     */
    public function getState()
    {
        $hash = array();
        foreach( $this as $property => $value )
        {
            if ( $property[0] === '_' )
                continue;

            if ( $value instanceof Interface_Serializable  || $value instanceof \ArrayAccess )
            {
                if ( !in_array( $property, $this->_aggregateMembers, true ) )
                    continue;

                if ( $value instanceof Interface_Serializable )
                {
                    $hash[$property] = $value->getState();
                    continue;
                }

                $hash[$property] = array();
                foreach ( $value as $key => $item )
                {
                    if ( $item instanceof Interface_Serializable )
                        $hash[$property][$key] = $item->getState();
                    else
                        $hash[$property][$key] = $item;
                }

            }
            else
                $hash[$property] = $value;
        }
        return $hash;
    }

    static protected function recursiveGetState( $item )
    {

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
        if ( isset( $this->$name ) && $name[0] !== '_' )
        {
            return $this->$name;
        }
        throw new \InvalidArgumentException( "{$name} is not a valid property on " . get_class( $this ) );
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
        if ( isset( $this->$name ) && $name[0] !== '_' )
        {
            if ( in_array( $name, $this->_readonly, true ) )
                throw new \InvalidArgumentException( "{$name} is a readonly property on " . get_class( $this ) );

            $this->$name = $value;
        }
        else
            throw new \InvalidArgumentException( "{$name} is not a valid property on " . get_class( $this ) );
    }
}
