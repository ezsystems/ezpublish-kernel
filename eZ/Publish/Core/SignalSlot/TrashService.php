<?php
/**
 * TrashService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\TrashService as TrashServiceInterface;

/**
 * TrashService class
 * @package eZ\Publish\Core\SignalSlot
 */
class TrashService implements TrashServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\TrashService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( TrashServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Loads a trashed location object from its $id.
     *
     * Note that $id is identical to original location, which has been previously trashed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read the trashed location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the location with the given id does not exist
     *
     * @param integer $trashItemId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem( $trashItemId )
    {
        $returnValue = $this->service->loadTrashItem( $trashItemId );
        return $returnValue;
    }

    /**
     * Sends $location and all its children to trash and returns the corresponding trash item.
     *
     * Content is left untouched.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to trash the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function trash( \eZ\Publish\API\Repository\Values\Content\Location $location )
    {
        $returnValue = $this->service->trash( $location );
        $this->signalDispatcher->emit(
            new Signal\TrashService\TrashSignal( array(
                'locationId' => $location->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Recovers the $trashedLocation at its original place if possible.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to recover the trash item at the parent location location
     *
     * If $newParentLocation is provided, $trashedLocation will be restored under it.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created or recovered location
     */
    public function recover( \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem, \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation = null )
    {
        $returnValue = $this->service->recover( $trashItem, $newParentLocation );
        $this->signalDispatcher->emit(
            new Signal\TrashService\RecoverSignal( array(
                'trashItemId' => $trashItem->id,
                'newParentLocationId' => ( $newParentLocation !== null ? $newParentLocation->id : null ),
            ) )
        );
        return $returnValue;
    }

    /**
     * Empties trash.
     *
     * All locations contained in the trash will be removed. Content objects will be removed
     * if all locations of the content are gone.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to empty the trash
     */
    public function emptyTrash()
    {
        $returnValue = $this->service->emptyTrash();
        $this->signalDispatcher->emit(
            new Signal\TrashService\EmptyTrashSignal( array(
            ) )
        );
        return $returnValue;
    }

    /**
     * Deletes a trash item.
     *
     * The corresponding content object will be removed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete this trash item
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     */
    public function deleteTrashItem( \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem )
    {
        $returnValue = $this->service->deleteTrashItem( $trashItem );
        $this->signalDispatcher->emit(
            new Signal\TrashService\DeleteTrashItemSignal( array(
                'trashItemId' => $trashItem->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Returns a collection of Trashed locations contained in the trash.
     *
     * $query allows to filter/sort the elements to be contained in the collection.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SearchResult
     */
    public function findTrashItems( \eZ\Publish\API\Repository\Values\Content\Query $query )
    {
        $returnValue = $this->service->findTrashItems( $query );
        return $returnValue;
    }

}
