<?php
/**
 * File contains Type Collection class
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Type Collection class, collection only accepts new elements of a certain type
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
class TypeCollection extends ReadOnlyCollection
{
    /**
     * @var string The class name (including namespace) to accept as input
     */
    private $type;

    /**
     * Construct object and assign internal array values
     *
     * @throws \InvalidArgumentException If elements contains item of wrong type
     * @param string $type
     * @param array $elements
     */
    public function __construct( $type, array $elements = array() )
    {
        foreach ( $elements as $item )
        {
            if ( !$item instanceof $type )
                throw new \InvalidArgumentException( "This collection is only accept '{$type}', '" . get_class( $item ) . '\' given.' );
        }
        $this->type = $type;
        parent::__construct( $elements );
    }

    /**
     * Overrides offsetSet to check type and allow if correct
     *
     * @throws \InvalidArgumentException On wrong type
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        if ( !$value instanceof $this->type )
            throw new \InvalidArgumentException( "This collection is only accept '{$this->type}', '" . get_class( $value ) . '\' given.' );

        if ( $offset === null )
            $this->elements[] = $value;
        else
            $this->elements[$offset] = $value;
    }

    /**
     * Unset a offset in collection
     *
     * @param string|int $offset
     */
    public function offsetUnset( $offset )
    {
        unset( $this->elements[$offset] );
    }

    /**
     * Return count of elements
     *
     * @return int
     */
    public function count()
    {
        return count( $this->elements );
    }
}

?>
