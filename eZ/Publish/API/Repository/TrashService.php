<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Trash\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Trash service, used for managing trashed content.
 */
interface TrashService
{
    /**
     * Loads a trashed location object from its $id.
     *
     * Note that $id is identical to original location, which has been previously trashed
     *
     * @param int $trashItemId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read the trashed location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the location with the given id does not exist
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem(int $trashItemId): TrashItem;

    /**
     * Sends $location and all its children to trash and returns the corresponding trash item.
     *
     * The current user may not have access to the returned trash item, check before using it.
     * Content is left untouched.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to trash the given location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem|null null if location was deleted, otherwise TrashItem
     */
    public function trash(Location $location): ?TrashItem;

    /**
     * Recovers the $trashedLocation at its original place if possible.
     *
     * If $newParentLocation is provided, $trashedLocation will be restored under it.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to recover the trash item at the parent location location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created or recovered location
     */
    public function recover(TrashItem $trashItem, Location $newParentLocation = null): Location;

    /**
     * Empties trash.
     *
     * All locations contained in the trash will be removed. Content objects will be removed
     * if all locations of the content are gone.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to empty the trash
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList
     */
    public function emptyTrash(): TrashItemDeleteResultList;

    /**
     * Deletes a trash item.
     *
     * The corresponding content object will be removed
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete this trash item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult
     */
    public function deleteTrashItem(TrashItem $trashItem): TrashItemDeleteResult;

    /**
     * Returns a collection of Trashed locations contained in the trash, which are readable by the current user.
     *
     * $query allows to filter/sort the elements to be contained in the collection.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Trash\SearchResult
     */
    public function findTrashItems(Query $query): SearchResult;
}
