<?php
/**
 * @package ezp\PublicAPI\Values
 */
namespace ezp\PublicAPI\Values;
use ezp\Base\Exception\PropertyNotFound;

/**
 *
 * The base class for all values
 * @package ezp\PublicAPI\Values
 */
abstract class ValueObject
{
    /**
     * Construct object optionally with set of properties
     *
     * @param array $properties
     */
    public function __construct( array $properties = array() )
    {
        foreach ( $properties as $property => $value )
        $this->$property = $value;
    }

    /**
     * Throws exception on all writes to undefined properties so typos are not silently accepted.
     *
     * @throws PropertyNotFound
     * @param string $name
     * @param string $value
     * @return void
     */
    public function __set( $name, $value )
    {
        throw new PropertyNotFound( $name, get_class( $this ) );
    }

    /**
     * Throws exception on all reads to undefined properties so typos are not silently accepted.
     *
     * @throws PropertyNotFound
     * @param string $name
     * @return void
     */
    public function __get( $name )
    {
        throw new PropertyNotFound( $name, get_class( $this ) );
    }

}
?>
