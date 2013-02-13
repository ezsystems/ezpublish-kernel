<?php
/**
 * File containing the Trash Handler interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Location\Trash;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * The Trash Handler interface defines operations on Location elements in the storage engine.
 */
interface Handler
{
    /**
     * Loads the data for the trashed location identified by $id.
     * $id is the same as original location (which has been previously trashed)
     *
     * @param int $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trashed
     */
    public function loadTrashItem( $id );

    /**
     * Sends a subtree starting to $locationId to the trash
     * and returns a Trashed object corresponding to $locationId.
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param mixed $locationId
     *
     * @return null|\eZ\Publish\SPI\Persistence\Content\Location\Trashed null if location was deleted, otherwise Trashed object
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
     * @param mixed $trashedId
     * @param mixed $newParentId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $newParentId is invalid
     *
     * @return int Newly restored location id
     */
    public function recover( $trashedId, $newParentId );

    /**
     * Returns an array of all trashed locations satisfying the $criterion (if provided),
     * sorted with SortClause objects contained in $sort (if any).
     * If no criterion is provided (null), no filter is applied
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param int $offset Offset to start listing from, 0 by default
     * @param int $limit Limit for the listing. Null by default (no limit)
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sort
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trashed[]
     */
    public function findTrashItems( Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null );

    /**
     * Empties the trash
     * Everything contained in the trash must be removed
     *
     * @return void
     */
    public function emptyTrash();

    /**
     * Removes a trashed location identified by $trashedLocationId from trash
     * Associated content has to be deleted
     *
     * @param int $trashedId
     *
     * @return void
     */
    public function deleteTrashItem( $trashedId );
}
