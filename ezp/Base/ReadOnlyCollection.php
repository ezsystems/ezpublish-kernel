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
namespace ezp\Base;
class ReadOnlyCollection extends \ArrayObject implements Interfaces\Collection
{
    /**
     * Overloads offsetSet() to do exception about being read only.
     *
     * @internal
     * @throws Exception\ReadOnly
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        throw new Exception\ReadOnly( 'Collection' );
    }

    /**
     * Overloads offsetUnset() to do exception about being read only.
     *
     * @internal
     * @throws Exception\ReadOnly
     * @param string|int $offset
     */
    public function offsetUnset( $offset )
    {
        throw new Exception\ReadOnly( 'Collection' );
    }

    /**
     * Overloads exchangeArray() to do exception about being read only.
     *
     * @throws Exception\ReadOnly
     * @param array $input
     * @return array
     */
    public function exchangeArray( $input )
    {
        throw new Exception\ReadOnly( 'Collection' );
    }
}

?>
