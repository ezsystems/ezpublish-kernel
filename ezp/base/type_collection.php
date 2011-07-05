<?php
/**
 * File contains Type Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\base;

/**
 * Type Collection class, collection only accepts new elements of a certain type
 *
 * @package ezp
 * @subpackage base
 */
class TypeCollection extends \ArrayObject implements CollectionInterface
{
    /**
     * @var string The class name (including namespace) to accept as input
     */
    private $type;

    /**
     * Construct object and assign internal array values
     *
     * A type strict collection that throws exception if type is wrong when appended to.
     *
     * @throws \InvalidArgumentException If elements contains item of wrong type
     * @param string $type
     * @param array $elements
     */
    public function __construct( $type, array $elements = array() )
    {
        $this->type = $type;
        foreach ( $elements as $item )
        {
            if ( !$item instanceof $type )
                throw new \InvalidArgumentException( "This collection only accepts '{$type}', '" .
                                                     ( is_object( $item ) ? get_class( $item ): gettype( $item ) ) . '\' given.' );
        }
        parent::__construct( $elements );
    }

    /**
     * Overrides offsetSet to check type and allow if correct
     *
     * @internal
     * @throws \InvalidArgumentException On wrong type
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        // throw if wrong type
        if ( !$value instanceof $this->type )
            throw new \InvalidArgumentException( "This collection only accepts '{$this->type}', '" .
                                                     ( is_object( $value ) ? get_class( $value ): gettype( $value ) ) . '\' given.' );

        // stop if value is already in array
        if ( in_array( $value, $this->getArrayCopy(), true ) )
            return;

        parent::offsetSet( $offset, $value );
    }

    /**
     * Overloads exchangeArray() to do type checks on input.
     *
     * @throws \InvalidArgumentException
     * @param array $input
     * @return array
     */
    public function exchangeArray( $input )
    {
        foreach ( $input as $item )
        {
            if ( !$item instanceof $this->type )
                throw new \InvalidArgumentException( "This collection only accepts '{$this->type}', '" .
                                                     ( is_object( $item ) ? get_class( $item ): gettype( $item ) ) . '\' given.' );
        }
        return parent::exchangeArray( $input );
    }
}

?>
