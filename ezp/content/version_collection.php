<?php
/**
 * File containing the ezp\Content\VersionCollection class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a collection of Content Versions
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class VersionCollection extends BaseCollection implements \ezp\DomainObjectInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array(Version)
     */
    protected $versions = array();

    public function __construct()
    {

    }

    /**
     * Returns the number of fieldsets available
     * @return int
     */
    public function count()
    {
        return count( $this->versions );
    }

    /**
     * Returns the iterator for this object
     * @return Iterator
     */
    public function getIterator()
    {
        // TODO : Use a dedicated iterator
        return new \ArrayIterator( $this );
    }

    public function offsetExists( $offset )
    {
        return isset( $this->versions[$offset] );
    }

    public function offsetGet( $offset )
    {
        return $this->versions[$offset];
    }

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetSet( $offset, $value )
    {
        throw new \ezcBasePropertyPermissionException( "versions", \ezcBasePropertyPermissionException::READ );
    }

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetUnset( $offset )
    {
        throw new \ezcBasePropertyPermissionException( "versions", \ezcBasePropertyPermissionException::READ );
    }
}
?>