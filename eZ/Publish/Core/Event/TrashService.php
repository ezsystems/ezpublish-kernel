<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Events\Trash\BeforeDeleteTrashItemEvent;
use eZ\Publish\API\Repository\Events\Trash\BeforeEmptyTrashEvent;
use eZ\Publish\API\Repository\Events\Trash\BeforeRecoverEvent;
use eZ\Publish\API\Repository\Events\Trash\BeforeTrashEvent;
use eZ\Publish\API\Repository\Events\Trash\DeleteTrashItemEvent;
use eZ\Publish\API\Repository\Events\Trash\EmptyTrashEvent;
use eZ\Publish\API\Repository\Events\Trash\RecoverEvent;
use eZ\Publish\API\Repository\Events\Trash\TrashEvent;
use eZ\Publish\SPI\Repository\Decorator\TrashServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TrashService extends TrashServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        TrashServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function trash(Location $location)
    {
        $eventData = [$location];

        $beforeEvent = new BeforeTrashEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getResult();
        }

        $trashItem = $beforeEvent->isResultSet()
            ? $beforeEvent->getResult()
            : $this->innerService->trash($location);

        $this->eventDispatcher->dispatch(
            new TrashEvent($trashItem, ...$eventData)
        );

        return $trashItem;
    }

    public function recover(
        TrashItem $trashItem,
        ?Location $newParentLocation = null
    ) {
        $eventData = [
            $trashItem,
            $newParentLocation,
        ];

        $beforeEvent = new BeforeRecoverEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocation();
        }

        $location = $beforeEvent->hasLocation()
            ? $beforeEvent->getLocation()
            : $this->innerService->recover($trashItem, $newParentLocation);

        $this->eventDispatcher->dispatch(
            new RecoverEvent($location, ...$eventData)
        );

        return $location;
    }

    public function emptyTrash()
    {
        $beforeEvent = new BeforeEmptyTrashEvent();

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getResultList();
        }

        $resultList = $beforeEvent->hasResultList()
            ? $beforeEvent->getResultList()
            : $this->innerService->emptyTrash();

        $this->eventDispatcher->dispatch(
            new EmptyTrashEvent($resultList)
        );

        return $resultList;
    }

    public function deleteTrashItem(TrashItem $trashItem)
    {
        $eventData = [$trashItem];

        $beforeEvent = new BeforeDeleteTrashItemEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getResult();
        }

        $result = $beforeEvent->hasResult()
            ? $beforeEvent->getResult()
            : $this->innerService->deleteTrashItem($trashItem);

        $this->eventDispatcher->dispatch(
            new DeleteTrashItemEvent($result, ...$eventData)
        );

        return $result;
    }
}
