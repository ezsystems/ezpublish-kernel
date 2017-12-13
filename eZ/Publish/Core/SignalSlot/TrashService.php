<?php

/**
 * TrashService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\EmptyTrashSignal;
use eZ\Publish\Core\SignalSlot\Signal\TrashService\DeleteTrashItemSignal;

/**
 * TrashService class.
 */
class TrashService implements TrashServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\TrashService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(TrashServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
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
     * @param mixed $trashItemId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem($trashItemId)
    {
        return $this->service->loadTrashItem($trashItemId);
    }

    /**
     * Sends $location and all its children to trash and returns the corresponding trash item.
     *
     * The current user may not have access to the returned trash item, check before using it.
     * Content is left untouched.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to trash the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return null|\eZ\Publish\API\Repository\Values\Content\TrashItem null if location was deleted, otherwise TrashItem
     */
    public function trash(Location $location)
    {
        $returnValue = $this->service->trash($location);
        $this->signalDispatcher->emit(
            new TrashSignal(
                array(
                    'locationId' => $location->id,
                    'parentLocationId' => $location->parentLocationId,
                    'contentId' => $location->contentId,
                    'contentTrashed' => $returnValue instanceof TrashItem,
                )
            )
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
    public function recover(TrashItem $trashItem, Location $newParentLocation = null)
    {
        $newLocation = $this->service->recover($trashItem, $newParentLocation);
        $this->signalDispatcher->emit(
            new RecoverSignal(
                array(
                    'trashItemId' => $trashItem->id,
                    'contentId' => $trashItem->contentId,
                    'newParentLocationId' => $newParentLocation !== null ? $newParentLocation->id : null,
                    'newLocationId' => $newLocation->id,
                )
            )
        );

        return $newLocation;
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
        $this->signalDispatcher->emit(new EmptyTrashSignal(array()));

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
    public function deleteTrashItem(TrashItem $trashItem)
    {
        $returnValue = $this->service->deleteTrashItem($trashItem);
        $this->signalDispatcher->emit(
            new DeleteTrashItemSignal(
                array(
                    'trashItemId' => $trashItem->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Returns a collection of Trashed locations contained in the trash, which are readable by the current user.
     *
     * $query allows to filter/sort the elements to be contained in the collection.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Trash\SearchResult
     */
    public function findTrashItems(Query $query)
    {
        return $this->service->findTrashItems($query);
    }
}
