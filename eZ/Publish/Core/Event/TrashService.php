<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\TrashServiceDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\Event\Trash\BeforeDeleteTrashItemEvent;
use eZ\Publish\Core\Event\Trash\BeforeEmptyTrashEvent;
use eZ\Publish\Core\Event\Trash\BeforeRecoverEvent;
use eZ\Publish\Core\Event\Trash\BeforeTrashEvent;
use eZ\Publish\Core\Event\Trash\DeleteTrashItemEvent;
use eZ\Publish\Core\Event\Trash\EmptyTrashEvent;
use eZ\Publish\Core\Event\Trash\RecoverEvent;
use eZ\Publish\Core\Event\Trash\TrashEvent;
use eZ\Publish\Core\Event\Trash\TrashEvents;

class TrashService extends TrashServiceDecorator
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
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
        if ($this->eventDispatcher->dispatch(TrashEvents::BEFORE_TRASH, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getResult();
        }

        $trashItem = $beforeEvent->isResultSet()
            ? $beforeEvent->getResult()
            : parent::trash($location);

        $this->eventDispatcher->dispatch(
            TrashEvents::TRASH,
            new TrashEvent($trashItem, ...$eventData)
        );

        return $trashItem;
    }

    public function recover(
        TrashItem $trashItem,
        Location $newParentLocation = null
    ) {
        $eventData = [
            $trashItem,
            $newParentLocation,
        ];

        $beforeEvent = new BeforeRecoverEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(TrashEvents::BEFORE_RECOVER, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getLocation();
        }

        $location = $beforeEvent->hasLocation()
            ? $beforeEvent->getLocation()
            : parent::recover($trashItem, $newParentLocation);

        $this->eventDispatcher->dispatch(
            TrashEvents::RECOVER,
            new RecoverEvent($location, ...$eventData)
        );

        return $location;
    }

    public function emptyTrash()
    {
        $beforeEvent = new BeforeEmptyTrashEvent();
        if ($this->eventDispatcher->dispatch(TrashEvents::BEFORE_EMPTY_TRASH, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getResultList();
        }

        $resultList = $beforeEvent->hasResultList()
            ? $beforeEvent->getResultList()
            : parent::emptyTrash();

        $this->eventDispatcher->dispatch(
            TrashEvents::EMPTY_TRASH,
            new EmptyTrashEvent($resultList)
        );

        return $resultList;
    }

    public function deleteTrashItem(TrashItem $trashItem)
    {
        $eventData = [$trashItem];

        $beforeEvent = new BeforeDeleteTrashItemEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(TrashEvents::BEFORE_DELETE_TRASH_ITEM, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getResult();
        }

        $result = $beforeEvent->hasResult()
            ? $beforeEvent->getResult()
            : parent::deleteTrashItem($trashItem);

        $this->eventDispatcher->dispatch(
            TrashEvents::DELETE_TRASH_ITEM,
            new DeleteTrashItemEvent($result, ...$eventData)
        );

        return $result;
    }
}
