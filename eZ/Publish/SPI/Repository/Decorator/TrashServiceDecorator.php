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

abstract class TrashServiceDecorator implements TrashService
{
    /** @var \eZ\Publish\API\Repository\TrashService */
    protected $innerService;

    public function __construct(TrashService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function loadTrashItem($trashItemId)
    {
        return $this->innerService->loadTrashItem($trashItemId);
    }

    public function trash(Location $location)
    {
        return $this->innerService->trash($location);
    }

    public function recover(
        TrashItem $trashItem,
        Location $newParentLocation = null
    ) {
        return $this->innerService->recover($trashItem, $newParentLocation);
    }

    public function emptyTrash()
    {
        return $this->innerService->emptyTrash();
    }

    public function deleteTrashItem(TrashItem $trashItem)
    {
        return $this->innerService->deleteTrashItem($trashItem);
    }

    public function findTrashItems(Query $query)
    {
        return $this->innerService->findTrashItems($query);
    }
}
