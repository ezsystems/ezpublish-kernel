<?php
/**
 * File contains Type Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Collection;
use ezp\Base\Collection,
    ezp\Base\Exception\InvalidArgumentType,
    ArrayObject;

/**
 * Type Collection class, collection only accepts new elements of a certain type
 *
 */
class Type extends ArrayObject implements Collection
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
     * @throws InvalidArgumentType If elements contains item of wrong type
     * @param string $type
     * @param array $elements
     */
    public function __construct( $type, array $elements = array() )
    {
        $this->type = $type;
        foreach ( $elements as $item )
        {
            if ( !$item instanceof $type )
                throw new InvalidArgumentType( 'elements', $type, $item );
        }
        parent::__construct( $elements );
    }

    /**
     * Returns the first index at which a given element can be found in the array, or false if it is not present.
     *
     * Uses strict comparison.
     *
     * @param mixed $item
     * @return int|string|false False if nothing was found
     */
    public function indexOf( $item )
    {
        if ( !$item instanceof $this->type )
            return false;

        foreach ( $this as $key => $value )
        {
            if ( $item->id === null )
            {
                if ( $value === $item )
                    return $key;
            }
            else if ( $value->id === $item->id )
            {
                return $key;
            }
        }
        return false;
    }

    /**
     * Overrides offsetSet to check type and allow if correct
     *
     * @internal
     * @throws InvalidArgumentType On wrong type
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        // throw if wrong type
        if ( !$value instanceof $this->type )
            throw new InvalidArgumentType( 'value', $this->type, $value );

        // stop if value is already in array
        if ( $this->indexOf( $value ) !== false )
            return;

        parent::offsetSet( $offset, $value );
    }

    /**
     * Overloads exchangeArray() to do type checks on input.
     *
     * @throws InvalidArgumentType
     * @param array $input
     * @return array
     */
    public function exchangeArray( $input )
    {
        foreach ( $input as $item )
        {
            if ( !$item instanceof $this->type )
                throw new InvalidArgumentType( 'input', $this->type, $item );
        }
        return parent::exchangeArray( $input );
    }
}

?>
