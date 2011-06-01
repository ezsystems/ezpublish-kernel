<?php
/**
 * File containing ezp\Content\Base class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * Abstract base class for Content namespace
 * @access private
 */
namespace ezp\Content;
use ezcBasePropertyNotFoundException;
use ezcBasePropertyPermissionException;

abstract class Base
{
    /**
     * Array container for virtual properties
     * Key is property name, value its value
     * @var array
     */
    protected $properties;

    /**
     * Array containing read-only property names
     * Those properties should not be modified via a magic setter
     * Key is the property name, value is a bool (always true)
     * @var array
     */
    protected $readOnlyProperties;

    /**
     * Array container for virtual properties, handled dynamically by methods
     * Key is property name, value is a bool (always true).
     *
     * Corresponding get method name must follow pattern doGet<propertyName>().
     * The method will be called without any parameter
     * e.g. : for a dynamic property named "myProperty", method should be "doGetMyProperty()".
     *
     * If the dynamic property is writeable, a set method should be defined.
     * Corresponding set method name must follow pattern doSet<propertyName>( $value ).
     * The method will be called with only one $value parameter.
     * e.g. : for a dynamic property named "myProperty", method should be "doSetMyProperty( $value )"
     *
     * @var array
     */
    protected $dynamicProperties;

    /**
     * Magic getter
     * @param string $property Property name
     * @access private
     * @throws ezcBasePropertyNotFoundException If $property cannot be found
     */
    public function __get( $property )
    {
        if ( isset( $this->properties[$property] ) )
        {
            return $this->properties[$property];
        }

        if ( isset( $this->dynamicProperties[$property] ) )
        {
            $property = ucfirst( $property );
            $method = "doGet{$property}";
            if ( method_exists( $this, $method ) )
            {
                return $this->$method();
            }
        }

        throw new ezcBasePropertyNotFoundException( $property );
    }

    /**
     * Magic setter
     * Avoids to modify read-only properties
     * @param string $property
     * @param mixed $value
     * @throws ezcBasePropertyNotFoundException If $property cannot be found
     * @throws ezcBasePropertyPermissionException When trying to set a value to a read-only property
     */
    public function __set( $property, $value )
    {
        if ( isset( $this->properties[$property] ) )
        {
            // First check if property has write access
            if ( isset( $this->readOnlyProperties[$property] ) )
            {
                throw new ezcBasePropertyPermissionException( $property, ezcBasePropertyPermissionException::READ );
            }

            $this->properties[$property] = $value;
        }
        else if ( isset( $this->dynamicProperties[$property] ) )
        {
            $property = ucfirst( $property );
            $method = "doSet{$property}";
            if ( method_exists( $this, $method ) )
            {
                $this->$method( $value );
            }
        }
        else
        {
            throw new ezcBasePropertyNotFoundException( $property );
        }

        return true;
    }

    /**
     * Checks if a public virtual property is set
     * @param string $property Property name
     * @access private
     */
    public function __isset( $property )
    {
        return isset( $this->properties[$property] ) || isset( $this->dynamicProperties[$property] );
    }
}

?>
