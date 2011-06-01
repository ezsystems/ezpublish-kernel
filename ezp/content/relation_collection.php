<?php
/**
 * File containing the ezp\Content\RelationCollection class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Collection of Content Relations
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class RelationCollection extends BaseCollection implements ContentDomainInterface, \IteratorAggregate, \Countable
{
    protected $relations = array();

    public function getIterator()
    {
        return new \ArrayIterator( $this );
    }

    public function count()
    {
        return count( $this->relations );
    }
}
?>