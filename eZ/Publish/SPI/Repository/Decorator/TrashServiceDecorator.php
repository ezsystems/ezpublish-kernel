<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\TrashService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\Trash\SearchResult;

abstract class TrashServiceDecorator implements TrashService
{
    /** @var \eZ\Publish\API\Repository\TrashService */
    protected $innerService;

    public function __construct(TrashService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function loadTrashItem(int $trashItemId): TrashItem
    {
        return $this->innerService->loadTrashItem($trashItemId);
    }

    public function trash(Location $location): ?TrashItem
    {
        return $this->innerService->trash($location);
    }

    public function recover(
        TrashItem $trashItem,
        Location $newParentLocation = null
    ): Location {
        return $this->innerService->recover($trashItem, $newParentLocation);
    }

    public function emptyTrash(): TrashItemDeleteResultList
    {
        return $this->innerService->emptyTrash();
    }

    public function deleteTrashItem(TrashItem $trashItem): TrashItemDeleteResult
    {
        return $this->innerService->deleteTrashItem($trashItem);
    }

    public function findTrashItems(Query $query): SearchResult
    {
        return $this->innerService->findTrashItems($query);
    }
}
