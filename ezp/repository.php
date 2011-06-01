<?php
/**
 * File containing the ezp\Content\Repository class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package Content
 */

/**
 * The eZ Content Repository, object that manages the Content Domain objects
 * @package Content
 */
namespace ezp;

class Repository
{
    /**
     * Loads the object of type $type with id $id
     *
     * @param string $type Examples: Content, Location...
     *
     * @return DomainObject
     */
    public function load( $type, $id )
    {

    }

    /**
     * Deletes the object $object
     *
     * @param DomainObject $object
     */
    public function delete( DomainObject $object )
    {

    }

    /**
     * Stores the object $object
     *
     * If the object doesn't exist, it is created. If it doesn't, it is created.
     *
     * @param DomainbObject $object
     */
    public function store( DomainObject $object )
    {

    }
}
?>