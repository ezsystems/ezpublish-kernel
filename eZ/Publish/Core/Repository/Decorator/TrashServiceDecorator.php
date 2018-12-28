<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\TrashService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\TrashItem;

class TrashServiceDecorator implements TrashService
{
    /**
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\TrashService $service
     */
    public function __construct(TrashService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function loadTrashItem($trashItemId)
    {
        return $this->service->loadTrashItem($trashItemId);
    }

    /**
     * {@inheritdoc}
     */
    public function trash(Location $location)
    {
        return $this->service->trash($location);
    }

    /**
     * {@inheritdoc}
     */
    public function recover(TrashItem $trashItem, Location $newParentLocation = null)
    {
        return $this->service->recover($trashItem, $newParentLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function emptyTrash()
    {
        return $this->service->emptyTrash();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTrashItem(TrashItem $trashItem)
    {
        return $this->service->deleteTrashItem($trashItem);
    }

    /**
     * {@inheritdoc}
     */
    public function findTrashItems(Query $query)
    {
        return $this->service->findTrashItems($query);
    }
}
