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
class ReadOnlyCollection extends \ArrayObject implements CollectionInterface
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
        throw new \InvalidArgumentException( "This collection is readonly and offset:{$offset} can not be Set" );
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
        throw new \InvalidArgumentException( "This collection is readonly and offset:{$offset} can not be Unset" );
    }

    /**
     * Overloads exchangeArray() to do exception about being read only.
     *
     * @throws \InvalidArgumentException
     * @param array $input
     * @return array
     */
    public function exchangeArray( $input )
    {
        throw new \InvalidArgumentException( "This collection is readonly and array can not be exchanged" );
    }
}

?>
