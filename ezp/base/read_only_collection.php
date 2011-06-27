<?php
/**
 * File contains Read Only Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Read Only Collection class
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
class ReadOnlyCollection implements CollectionInterface
{
    /**
     * @var array Internal native array.
     */
    protected $elements = array();

    /**
     * @var int For storing count value as it never changes.
     */
    protected $count;

    /**
     * Construct object and assign internal array values
     *
     * @param array $elements
     */
    public function __construct( array $elements = array() )
    {
        $this->elements = $elements;
    }

    /**
     * Get Iterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator( $this->elements );
    }

    /**
     * Set value on a offset in collection.
     *
     * @throws \InvalidArgumentException This collection is readonly
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        throw new \InvalidArgumentException( "This collection is readonly and offset:{$offset} can not be Set " );
    }

    /**
     * Get value in collection by offset, return null if not set.
     *
     * @param string|int $offset
     * @return mixed
     */
    public function offsetGet( $offset )
    {
        if ( isset($this->elements[$offset]) )
            return $this->elements[$offset];
        return null;
    }

    /**
     * Unset value on a offset in collection
     *
     * @throws \InvalidArgumentException This collection is readonly
     * @param string|int $offset
     */
    public function offsetUnset( $offset )
    {
        throw new \InvalidArgumentException( "This collection is readonly and offset:{$offset} can not be Unset " );
    }

    /**
     * Checks if value exists on a offset in collection
     *
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists( $offset )
    {
        return isset( $this->elements[$offset] );
    }

    /**
     * Return count of elements
     *
     * @return int
     */
    public function count()
    {
        if ( $this->count === null )
        {
            $this->count = count( $this->elements );
        }
        return $this->count;
    }
}

?>
