<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\Core\Event\TrashService;
use eZ\Publish\Core\Event\Trash\DeleteTrashItemEvent;
use eZ\Publish\Core\Event\Trash\BeforeDeleteTrashItemEvent;
use eZ\Publish\Core\Event\Trash\EmptyTrashEvent;
use eZ\Publish\Core\Event\Trash\BeforeEmptyTrashEvent;
use eZ\Publish\Core\Event\Trash\RecoverEvent;
use eZ\Publish\Core\Event\Trash\BeforeRecoverEvent;
use eZ\Publish\Core\Event\Trash\TrashEvent;
use eZ\Publish\Core\Event\Trash\BeforeTrashEvent;

class TrashServiceTest extends AbstractServiceTest
{
    public function testEmptyTrashEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEmptyTrashEvent::class,
            EmptyTrashEvent::class
        );

        $parameters = [
        ];

        $resultList = $this->createMock(TrashItemDeleteResultList::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('emptyTrash')->willReturn($resultList);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->emptyTrash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($resultList, $result);
        $this->assertSame($calledListeners, [
            [BeforeEmptyTrashEvent::class, 0],
            [EmptyTrashEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnEmptyTrashResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEmptyTrashEvent::class,
            EmptyTrashEvent::class
        );

        $parameters = [
        ];

        $resultList = $this->createMock(TrashItemDeleteResultList::class);
        $eventResultList = $this->createMock(TrashItemDeleteResultList::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('emptyTrash')->willReturn($resultList);

        $traceableEventDispatcher->addListener(BeforeEmptyTrashEvent::class, function (BeforeEmptyTrashEvent $event) use ($eventResultList) {
            $event->setResultList($eventResultList);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->emptyTrash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventResultList, $result);
        $this->assertSame($calledListeners, [
            [BeforeEmptyTrashEvent::class, 10],
            [BeforeEmptyTrashEvent::class, 0],
            [EmptyTrashEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testEmptyTrashStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEmptyTrashEvent::class,
            EmptyTrashEvent::class
        );

        $parameters = [
        ];

        $resultList = $this->createMock(TrashItemDeleteResultList::class);
        $eventResultList = $this->createMock(TrashItemDeleteResultList::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('emptyTrash')->willReturn($resultList);

        $traceableEventDispatcher->addListener(BeforeEmptyTrashEvent::class, function (BeforeEmptyTrashEvent $event) use ($eventResultList) {
            $event->setResultList($eventResultList);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->emptyTrash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventResultList, $result);
        $this->assertSame($calledListeners, [
            [BeforeEmptyTrashEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeEmptyTrashEvent::class, 0],
            [EmptyTrashEvent::class, 0],
        ]);
    }

    public function testTrashEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeTrashEvent::class,
            TrashEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $trashItem = $this->createMock(TrashItem::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('trash')->willReturn($trashItem);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->trash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($trashItem, $result);
        $this->assertSame($calledListeners, [
            [BeforeTrashEvent::class, 0],
            [TrashEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnTrashResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeTrashEvent::class,
            TrashEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $trashItem = $this->createMock(TrashItem::class);
        $eventTrashItem = $this->createMock(TrashItem::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('trash')->willReturn($trashItem);

        $traceableEventDispatcher->addListener(BeforeTrashEvent::class, function (BeforeTrashEvent $event) use ($eventTrashItem) {
            $event->setResult($eventTrashItem);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->trash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventTrashItem, $result);
        $this->assertSame($calledListeners, [
            [BeforeTrashEvent::class, 10],
            [BeforeTrashEvent::class, 0],
            [TrashEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testTrashStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeTrashEvent::class,
            TrashEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $trashItem = $this->createMock(TrashItem::class);
        $eventTrashItem = $this->createMock(TrashItem::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('trash')->willReturn($trashItem);

        $traceableEventDispatcher->addListener(BeforeTrashEvent::class, function (BeforeTrashEvent $event) use ($eventTrashItem) {
            $event->setResult($eventTrashItem);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->trash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventTrashItem, $result);
        $this->assertSame($calledListeners, [
            [BeforeTrashEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeTrashEvent::class, 0],
            [TrashEvent::class, 0],
        ]);
    }

    public function testRecoverEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRecoverEvent::class,
            RecoverEvent::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('recover')->willReturn($location);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->recover(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($location, $result);
        $this->assertSame($calledListeners, [
            [BeforeRecoverEvent::class, 0],
            [RecoverEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnRecoverResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRecoverEvent::class,
            RecoverEvent::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('recover')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeRecoverEvent::class, function (BeforeRecoverEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->recover(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeRecoverEvent::class, 10],
            [BeforeRecoverEvent::class, 0],
            [RecoverEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRecoverStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRecoverEvent::class,
            RecoverEvent::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('recover')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeRecoverEvent::class, function (BeforeRecoverEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->recover(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeRecoverEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRecoverEvent::class, 0],
            [RecoverEvent::class, 0],
        ]);
    }

    public function testDeleteTrashItemEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTrashItemEvent::class,
            DeleteTrashItemEvent::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
        ];

        $result = $this->createMock(TrashItemDeleteResult::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('deleteTrashItem')->willReturn($result);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteTrashItem(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($result, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteTrashItemEvent::class, 0],
            [DeleteTrashItemEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteTrashItemResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTrashItemEvent::class,
            DeleteTrashItemEvent::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
        ];

        $result = $this->createMock(TrashItemDeleteResult::class);
        $eventResult = $this->createMock(TrashItemDeleteResult::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('deleteTrashItem')->willReturn($result);

        $traceableEventDispatcher->addListener(BeforeDeleteTrashItemEvent::class, function (BeforeDeleteTrashItemEvent $event) use ($eventResult) {
            $event->setResult($eventResult);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteTrashItem(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventResult, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteTrashItemEvent::class, 10],
            [BeforeDeleteTrashItemEvent::class, 0],
            [DeleteTrashItemEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteTrashItemStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTrashItemEvent::class,
            DeleteTrashItemEvent::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
        ];

        $result = $this->createMock(TrashItemDeleteResult::class);
        $eventResult = $this->createMock(TrashItemDeleteResult::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('deleteTrashItem')->willReturn($result);

        $traceableEventDispatcher->addListener(BeforeDeleteTrashItemEvent::class, function (BeforeDeleteTrashItemEvent $event) use ($eventResult) {
            $event->setResult($eventResult);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteTrashItem(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventResult, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteTrashItemEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteTrashItemEvent::class, 0],
            [DeleteTrashItemEvent::class, 0],
        ]);
    }
}
