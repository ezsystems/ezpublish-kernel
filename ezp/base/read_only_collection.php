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
 * Lets you create a collection based on an array, keys must be integers as this extends SplFixedArray
 * to preserve memory consumption.
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
class ReadOnlyCollection extends \SplFixedArray implements CollectionInterface
{
    /**
     * Set value on a offset in collection, read only collection so throws exception.
     *
     * @internal
     * @throws \InvalidArgumentException This collection is readonly
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        throw new \InvalidArgumentException( "This collection is readonly and offset:{$offset} can not be Set " );
    }

    /**
     * Unset value on a offset in collection
     *
     * @internal
     * @throws \InvalidArgumentException This collection is readonly
     * @param string|int $offset
     */
    public function offsetUnset( $offset )
    {
        throw new \InvalidArgumentException( "This collection is readonly and offset:{$offset} can not be Unset " );
    }

    /**
     * Return an instance of ReadOnlyCollection from array values
     *
     * Note: Indexes are not maintained!
     *
     * @param array $array
     * @param bool $save_indexes Not used, indexes is not maintained by this function!
     * @return ReadOnlyCollection
     */
    public static function fromArray( $array, $save_indexes = false )
    {
        $obj = new static( count( $array ) );
        return $obj->setArray( $array );
    }

    /**
     * Set array values on instance, for internal use, make sure size is set first!
     *
     * @internal
     * @param array $array
     * @return ReadOnlyCollection
     */
    protected function setArray( array $array )
    {
        $i = 0;
        foreach ( $array as $item )
        {
            parent::offsetSet( $i++, $item );
        }
        return $this;
    }
}

?>
