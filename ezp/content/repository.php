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
namespace ezp\Content;

class Repository extends ezp\Repository
{
    /**
     * Loads a content from it's $id
     *
     * @param int $id
     *
     * @return Content
     */
    public function loadContent( $id )
    {
        return parent::load( 'Content', $id );
    }

    /**
     * Loads the location with id $id
     *
     * @param int $id
     * @return Location
     */
    public function loadLocation( $id )
    {
        return parent::load( 'Location', $id );
    }


    /**
     * @return \ezp\Content\Services\Subtree
     */
    public function getSubtreeService()
    {
        return new Services\Subtree();
    }

    /**
     * @return \ezp\Content\Services\Trash
     */
    public function getTrashService()
    {
        return new Services\Trash();
    }

    /**
     * @return \ezp\Content\Services\Content
     */
    public function getContentService()
    {
        return new Services\Content();
    }
}
?>