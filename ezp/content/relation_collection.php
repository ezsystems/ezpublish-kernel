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

class RelationCollection extends BaseCollection implements DomainObjectInterface, IteratorAggregate, Countable
{
    protected $relations = array();

    /**
     * Restores the state of a content object
     * @param array $objectValue
     */
    public static function __set_state( array $state )
    {
        $obj = new self;
        foreach ( $state as $property => $value )
        {
            if ( isset( $obj->properties[$property] ) )
            {
                $obj->properties[$property] = $value;
            }
        }

        return $obj;
    }

    public function getIterator()
    {
        return new ArrayIterator( $this );
    }

    public function count()
    {
        return count( $this->relations );
    }
}
?>