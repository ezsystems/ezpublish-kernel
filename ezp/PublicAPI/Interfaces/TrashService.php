<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\Content\Location;
use ezp\PublicAPI\Values\Content\SearchResult;
use ezp\PublicAPI\Values\Content\TrashItem;

/**
 * Location service, used for complex subtree operations
 *
 * @package ezp\PublicAPI\Interfaces
 */
interface TrashService
{
    /**
     * Loads a trashed location object from its $id.
     * Note that $id is identical to original location, which has been previously trashed
     *
     * @param integer $trashItemId
     *
     * @return TrashItem
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to read the trashed location
     * @throws ezp\PublicAPI\Interfaces\NotFoundException - if the location with the given id does not exist
     */
    public function loadTrashItem( $trashItemId );

    /**
     * Sends $location and all its children to trash and returns the corresponding trash item
     * Content is left untouched.
     *
     * @param Location $location
     *
     * @return TrashItem
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to trash the given location
     */
    public function trash( /*Location*/ $location );

    /**
     * Recovers the $trashedLocation at its original place if possible.
     * If $newParentLocation is provided, $trashedLocation will be restored under it.
     *
     * @param TrashItem $trashItem
     * @param LocationCreate $newParentLocation
     *
     * @return Location the newly created or recovered location
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to recover the trash item at the parent location location
     */
    public function recover( /*TrashItem*/ $trashItem, /*LocationCreate*/ $newParentLocation = null );

    /**
     * Empties trash.
     * All locations contained in the trash will be removed. Content objects will be removed
     * if all locations of the content are gone.
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to empty the trash
     */
    public function emptyTrash();

    /**
     * Deletes a trash item
     * The corresponding content object will be removed
     *
     * @param TrashItem $trashItem
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to delete this trash item
     */
    public function deleteTrashItem( /*TrashItem*/ $trashItem );

    /**
     * Returns a collection of Trashed locations contained in the trash.
     * $query allows to filter/sort the elements to be contained in the collection.
     *
     * @param Query $query
     *
     * @return SearchResult
     */
    public function findTrashItems( /*Query*/ $query );
}
