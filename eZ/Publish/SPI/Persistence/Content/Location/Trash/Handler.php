<?php
/**
 * File containing the Trash Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Location\Trash;
use ezp\Persistence\Content\Location,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Content\Location\UpdateStruct,
    ezp\Persistence\Content\Query\Criterion;

/**
 * The Trash Handler interface defines operations on Location elements in the storage engine.
 *
 */
interface Handler
{
    /**
     * Loads the data for the trashed location identified by $id.
     * $id is the same as original location (which has been previously trashed)
     *
     * @param int $id
     * @return \ezp\Persistence\Content\Location\Trashed
     * @throws \ezp\Base\Exception\NotFound
     */
    public function load( $id );

    /**
     * Sends a subtree to the trash
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param mixed $locationId
     * @return \ezp\Persistence\Content\Location\Trashed
     */
    public function trashSubtree( $locationId );

    /**
     * Returns a trashed location to normal state.
     *
     * Recreates the originally trashed location in the new position.
     * If this is not possible (because the old location does not exist any more),
     * a ParentNotFound exception is thrown.
     *
     * Returns newly restored location Id.
     *
     * @param mixed $locationId
     * @param mixed $newParentId
     * @return int Newly restored location id
     * @throws \ezp\Content\Location\Exception\ParentNotFound
     */
    public function untrashLocation( $trashedId, $newParentId );

    /**
     * Returns an array of all trashed locations satisfying the $criterion (if provided),
     * sorted with SortClause objects contained in $sort (if any).
     * If no criterion is provided (null), no filter is applied
     *
     * @param \ezp\Persistence\Content\Query\Criterion $criterion
     * @param $offset Offset to start listing from, 0 by default
     * @param $limit Limit for the listing. Null by default (no limit)
     * @param \ezp\Persistence\Content\Query\SortClause[] $sort
     * @return \ezp\Persistence\Content\Location\Trashed[]
     */
    public function listTrashed( Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null );

    /**
     * Empties the trash
     * Everything contained in the trash must be removed
     */
    public function emptyTrash();

    /**
     * Removes a trashed location identified by $trashedLocationId from trash
     * Associated content has to be deleted
     *
     * @param int $trashedId
     */
    public function emptyOne( $trashedId );
}
