<?php
/**
 * File containing the ezp\Content\TranslationCollection class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Content translations collection
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class TranslationCollection extends BaseCollection implements ContentDomainInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array(Translation)
     */
    protected $translations = array();

    public function __construct()
    {

    }

    /**
     * Returns the number of fieldsets available
     * @return int
     */
    public function count()
    {
        return count( $this->translations );
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
        return isset( $this->translations[$offset] );
    }

    public function offsetGet( $offset )
    {
        return $this->translations[$offset];
    }

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetSet( $offset, $value )
    {
        throw new \ezcBasePropertyPermissionException( "translations", \ezcBasePropertyPermissionException::READ );
    }

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetUnset( $offset )
    {
        throw new \ezcBasePropertyPermissionException( "translations", \ezcBasePropertyPermissionException::READ );
    }
}
?>