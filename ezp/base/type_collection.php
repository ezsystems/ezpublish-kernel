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
class TypeCollection extends ReadOnlyCollection
{
    /**
     * @var string The class name (including namespace) to accept as input
     */
    private $type;

    /**
     * @var AbstractModel|null The object that holds this collection
     */
    private $attachedObject;

    /**
     * @var string|null Property on the other/owning side of the relation.
     */
    private $oppositeProperty;

    /**
     * Construct object and assign internal array values
     *
     * A type strict collection that throws exception if type is wrong when appended to,
     * also optionally supports making sure opposite side of relation is set when $object & $oppositeProperty
     * is defined.
     *
     * @throws \InvalidArgumentException If elements contains item of wrong type
     * @param string $type
     * @param AbstractModel|null $attachedObject The object that holds this collection
     * @param string|null $oppositeProperty Property on the other/owning side of the relation.
     * @param array $elements
     */
    public function __construct( $type, AbstractModel $attachedObject = null, $oppositeProperty = null, array $elements = array() )
    {
        $this->type = $type;
        $this->attachedObject = $attachedObject;
        $this->oppositeProperty = $oppositeProperty;
        foreach ( $elements as $item )
        {
            if ( !$item instanceof $type )
                throw new \InvalidArgumentException( "This collection is only accept '{$type}', '" . get_class( $item ) . '\' given.' );
        }
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
        // throw if wrong type
        if ( !$value instanceof $this->type )
            throw new \InvalidArgumentException( "This collection only accepts '{$this->type}', '" . get_class( $value ) . '\' given.' );

        // stop if value is already in array
        if ( in_array( $value, $this->elements, true ) )
            return;

        // append or set depending on $offset
        if ( $offset === null )
            $this->elements[] = $value;
        else
            $this->elements[$offset] = $value;

        // optionally: make sure owning/opposite side of relation is assigned as well.
        if ( $this->attachedObject !== null && $this->oppositeProperty !== null )
        {
            $property = $this->oppositeProperty;
            if ( $value->$property instanceof CollectionInterface )
            {
                $collection = $value->$property;
                $collection[] = $this->attachedObject;
            }
            else
            {
                $value->$property = $this->attachedObject;
            }
        }
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
