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
abstract class Abstract_Field_Value implements Interface_Field_Value, Interface_Serializable
{
    /**
     * Constant that Field types needs to defined
     * eg. ezstring
     * @var string
     */
    const FIELD_IDENTIFIER = '';

    /**
     * List of field type identifiers for use by design overrides
     * eg. ezstring
     * @var array
     */
    protected $types = array();

    /**
     * Constructor, appends $types and assign $value by reference
     *
     * @param mixed $value
     */
    public function __construct()
    {
    }

    /**
     * Assign $value by reference
     *
     * @param mixed $value As defined by defintion()
     */
    final public function assignValue( &$value )
    {
        // @todo Find a more suitable way to make sure changes go back to parent object
        // as this won't work with things like DateTime objects for instance.
        $this->value =& $value;
    }


    /**
     * Return list of identifiers for field type for design override use
     *
     * @return array
     */
    final public function typeInheritance()
    {
        return $this->types;
    }

    /**
     * Used by var_export and other functions to init class with all values
     *
     * @static
     * @param array $properties
     * @return Content
     */
    final public static function __set_state( array $properties )
    {
        $class = new static();
        return $class->setState( $properties );
    }


    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @param array $properties
     * @return Content Return $this
     */
    final public function setState( array $properties )
    {
        foreach ( $properties as $property => $value )
        {
            if ( $this->$property instanceof Interface_Serializable && !$value instanceof Interface_Serializable )
                $this->$property->setState( $value );
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
    final public function getState()
    {
        $hash = array();
        foreach( $this as $property => $value )
        {
            if ( $property instanceof Interface_Serializable )
                $hash[$property] = $value->getState();
            else
                $hash[$property] = $value;
        }
        return $hash;
    }
}
