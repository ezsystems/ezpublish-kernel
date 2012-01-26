<?php
/**
 * @package ezp\PublicAPI\Values
 */
namespace ezp\PublicAPI\Values;
use ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission;// @todo rename to PropertyReadOnly?

/**
 * The base class for all value objects and structs
 *
 * Supports readonly properties by marking them as protected.
 * In this case they will only be writable using constructor, and need to be documented
 * using @property-read <type> <$var> in class doc in addition to inline property doc.
 * Writable properties must be public and must be documented inline.
 *
 * @package ezp\PublicAPI\Values
 */
abstract class ValueObject
{
    /**
     * Construct object optionally with a set of properties
     *
     * Readonly properties values must be set using $properties as they are not writable anymore
     * after object has been created.
     *
     * @param array $properties
     */
    public function __construct( array $properties = array() )
    {
        foreach ( $properties as $property => $value )
        {
            $this->$property = $value;
        }
    }

    /**
     * Magic set function handling writes to non public properties
     *
     * Throws PropertyNotFound exception on all writes to undefined properties so typos are not silently accepted and
     * throws PropertyPermission exception on readonly (protected) properties.
     *
     * @param string $property Name of the property
     * @param string $value
     *
     * @return void
     *
     * @throws PropertyNotFound When property does not exist
     * @throws PropertyPermission When property is readonly (protected)
     */
    public function __set( $property, $value )
    {
        if ( property_exists( $this, $property ) )
        {
            throw new PropertyPermission( $property, PropertyPermission::READ, get_class( $this ) );
        }
        throw new PropertyNotFound( $property, get_class( $this ) );
    }

    /**
     * Magic get function handling read to non public properties
     *
     * Returns value for all readonly (protected) properties.
     * Throws PropertyNotFound exception on all reads to undefined properties so typos are not silently accepted.
     *
     * @param string $property Name of the property
     *
     * @return mixed
     *
     * @throws PropertyNotFound When property does not exist
     */
    public function __get( $property )
    {
        if ( property_exists( $this, $property ) )
        {
            return $this->$property;
        }
        throw new PropertyNotFound( $property, get_class( $this ) );
    }

}
