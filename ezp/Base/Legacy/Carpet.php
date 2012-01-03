<?php
/**
 * File containing the Carpet class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Legacy;
use ezp\Base\Exception\MethodNotFound,
    ezp\Base\Exception\PropertyNotFound,
    ReflectionClass;

/**
 * Utility class to "hide the dust under the carpet".
 * Use this class to abstract a class/object coming from legacy codebase (aka "old" eZ Publish)
 *
 * @internal
 */
class Carpet
{
    /**
     * Class name for the abstracted legacy object
     *
     * @var string
     */
    protected static $className;

    /**
     * Abstracted object coming from Legacy codebase
     * @var mixed
     */
    protected $object;

    /**
     * Constructor
     *
     * @param string $classToSweep Class name in the Legacy codebase to "sweep under the carpet"
     */
    public function __construct( $classToSweep )
    {
        static::$className = $classToSweep;
    }

    /**
     * "Lifts the carpet and sweeps the dust under it"
     * In other more pragmatic words, Instantiates the object to be abstracted.
     *
     * Note: This method makes use of Reflection if $constructorArgs contains more than 1 element.
     * Therefore, to avoid too much performance cost, please consider extending this class
     * and reimplement this method in order to pass the exact number of arguments
     * to the abstracted class's constructor
     *
     * @param array|null $constructorArgs Arguments to pass to the constructor.
     *                                    Set to null (default) if no argument is required
     * @return \ezp\Base\Legacy\Carpet
     *
     * @todo Fix inclusion of class files !
     */
    public function lift( array $constructorArgs = null )
    {
        if ( $constructorArgs === null )
        {
            $this->object = new $this->className;
        }
        else if ( count( $constructorArgs ) == 1 )
        {
            $this->object = new $this->className( $constructorArgs[0] );
        }
        else
        {
            $refClass = new ReflectionClass( $this->className );
            $this->object = $refClass->newInstanceArgs( $constructorArgs );
        }

        return $this;
    }

    /**
     * Access to abstracted object's property, identified by $name.
     *
     * @param string $name Property name
     * @return mixed
     * @throws \ezp\Base\Exception\PropertyNotFound
     */
    public function __get( $name )
    {
        if ( !property_exists( $this->object, $name ) )
            throw new PropertyNotFound( $name, $this->className );

        return $this->object->$name;
    }

    /**
     * Sets $value to abstracted object's property, identified by $name
     *
     * @param string $name Property name
     * @param mixed $value Value to set
     * @param \ezp\Base\Exception\PropertyNotFound
     */
    public function __set( $name, $value )
    {
        if ( !property_exists( $this->object, $name ) )
            throw new PropertyNotFound( $name, $this->className );

        $this->object->$name = $value;
    }

    /**
     * Calls $method with $arguments on abstracted object
     *
     * @param string $method Method name
     * @param array $arguments
     * @return mixed
     * @throws \ezp\Base\Exception\MethodNotFound
     */
    public function __call( $method, array $arguments )
    {
        if ( !method_exists( $this->object, $method ) )
            throw new MethodNotFound( $method, $this->className );

        return call_user_func_array( array( $this->object, $method ), $arguments );
    }

    /**
     * Calls static $method with $arguments on abstracted object class
     *
     * @param string $method Method name
     * @param array $arguments
     * @return mixed
     * @throws \ezp\Base\Exception\MethodNotFound
     */
    public static function __callStatic( $method, array $arguments )
    {
        if ( !method_exists( static::$className, $method ) )
            throw new MethodNotFound( $method, static::$className );

        return forward_static_call_array( array( static::$className, $method ), $arguments );
    }
}
