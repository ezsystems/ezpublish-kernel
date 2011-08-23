<?php
/**
 * File containing the Trash Handler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Location\Trash;
use ezp\Persistence\Content\Location,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Content\Location\UpdateStruct;

/**
 * The Trash Handler interface defines operations on Location elements in the storage engine.
 *
 */
interface Handler
{
    /**
     * Loads the data for the trashed location identified by $id.
     *
     * @param int $id
     * @return \ezp\Persistence\Content\Location\Trashed
     */
    public function load( $id );

    /**
     * Loads the data for the trashed location identified by $locationId.
     * $locationId is the original Id for trashed location
     *
     * @param int $locationId
     * @return \ezp\Persistence\Content\Location\Trashed
     */
    public function loadFromLocationId( $locationId );

    /**
     * Sends a subtree to the trash
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param mixed $locationId
     * @return boolean
     */
    public function trashSubtree( $locationId );

    /**
     * Returns a trashed location to normal state.
     *
     * Recreates the originally trashed location in the new position. If no new
     * position has been specified, it will be tried to re-create the location
     * at the old position. If this is not possible ( because the old location
     * does not exist any more) and exception is thrown.
     *
     * @param mixed $locationId
     * @param mixed $newParentId
     * @return boolean
     */
    public function untrashLocation( $trashedId, $newParentId = null );

    /**
     * Returns an array of all trashed locations
     *
     * @param $offset Offset to start listing from, 0 by default
     * @param $limit Limit for the listing. Null by default (no limit)
     * @return \ezp\Persistence\Content\Location\Trashed[]
     */
    public function listTrashed( $offset = 0, $limit = null );
}
