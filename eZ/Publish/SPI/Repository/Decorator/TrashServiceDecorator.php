<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\TrashService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\TrashItem;

abstract class TrashServiceDecorator implements TrashService
{
    /** @var eZ\Publish\API\Repository\TrashService */
    protected $innerService;

    /**
     * @param eZ\Publish\API\Repository\TrashService
     */
    public function __construct(TrashService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function loadTrashItem($trashItemId)
    {
        $this->innerService->loadTrashItem($trashItemId);
    }

    public function trash(Location $location)
    {
        $this->innerService->trash($location);
    }

    public function recover(TrashItem $trashItem, Location $newParentLocation = null)
    {
        $this->innerService->recover($trashItem, $newParentLocation);
    }

    public function emptyTrash()
    {
        $this->innerService->emptyTrash();
    }

    public function deleteTrashItem(TrashItem $trashItem)
    {
        $this->innerService->deleteTrashItem($trashItem);
    }

    public function findTrashItems(Query $query)
    {
        $this->innerService->findTrashItems($query);
    }
}
