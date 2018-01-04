<?php

/**
 * TrashService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * TrashService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class TrashService implements TrashServiceInterface
{
    /** @var \eZ\Publish\API\Repository\TrashService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\TrashService $service
     */
    public function __construct(
        TrashServiceInterface $service
    ) {
        $this->service = $service;
    }

    public function loadTrashItem($trashItemId)
    {
        return $this->service->loadTrashItem($trashItemId);
    }

    public function trash(Location $location)
    {
        return $this->service->trash($location);
    }

    public function recover(TrashItem $trashItem, Location $newParentLocation = null)
    {
        return $this->service->recover($trashItem, $newParentLocation);
    }

    public function emptyTrash()
    {
        return $this->service->emptyTrash();
    }

    public function deleteTrashItem(TrashItem $trashItem)
    {
        return $this->service->deleteTrashItem($trashItem);
    }

    public function findTrashItems(Query $query)
    {
        return $this->service->findTrashItems($query);
    }
}
