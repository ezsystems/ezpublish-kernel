<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Trash\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;

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

    public function loadTrashItem(int $trashItemId): TrashItem
    {
        return $this->service->loadTrashItem($trashItemId);
    }

    public function trash(Location $location): ?TrashItem
    {
        return $this->service->trash($location);
    }

    public function recover(TrashItem $trashItem, Location $newParentLocation = null): Location
    {
        return $this->service->recover($trashItem, $newParentLocation);
    }

    public function emptyTrash(): TrashItemDeleteResultList
    {
        return $this->service->emptyTrash();
    }

    public function deleteTrashItem(TrashItem $trashItem): TrashItemDeleteResult
    {
        return $this->service->deleteTrashItem($trashItem);
    }

    public function findTrashItems(Query $query): SearchResult
    {
        return $this->service->findTrashItems($query);
    }
}
