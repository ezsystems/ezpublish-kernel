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
use ezp\Repository as BaseRepository;

class Repository extends BaseRepository
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

    }

    /**
     * Loads the location with id $id
     *
     * @param int $id
     * @return Location
     */
    public function loadLocation( $id )
    {

    }
}
?>