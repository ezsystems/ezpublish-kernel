<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\Trash\BeforeDeleteTrashItemEvent as BeforeDeleteTrashItemEventInterface;
use eZ\Publish\API\Repository\Events\Trash\BeforeEmptyTrashEvent as BeforeEmptyTrashEventInterface;
use eZ\Publish\API\Repository\Events\Trash\BeforeRecoverEvent as BeforeRecoverEventInterface;
use eZ\Publish\API\Repository\Events\Trash\BeforeTrashEvent as BeforeTrashEventInterface;
use eZ\Publish\API\Repository\Events\Trash\DeleteTrashItemEvent as DeleteTrashItemEventInterface;
use eZ\Publish\API\Repository\Events\Trash\EmptyTrashEvent as EmptyTrashEventInterface;
use eZ\Publish\API\Repository\Events\Trash\RecoverEvent as RecoverEventInterface;
use eZ\Publish\API\Repository\Events\Trash\TrashEvent as TrashEventInterface;
use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\Event\TrashService;

class TrashServiceTest extends AbstractServiceTest
{
    public function testEmptyTrashEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEmptyTrashEventInterface::class,
            EmptyTrashEventInterface::class
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
            [BeforeEmptyTrashEventInterface::class, 0],
            [EmptyTrashEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnEmptyTrashResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEmptyTrashEventInterface::class,
            EmptyTrashEventInterface::class
        );

        $parameters = [
        ];

        $resultList = $this->createMock(TrashItemDeleteResultList::class);
        $eventResultList = $this->createMock(TrashItemDeleteResultList::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('emptyTrash')->willReturn($resultList);

        $traceableEventDispatcher->addListener(BeforeEmptyTrashEventInterface::class, function (BeforeEmptyTrashEventInterface $event) use ($eventResultList) {
            $event->setResultList($eventResultList);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->emptyTrash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventResultList, $result);
        $this->assertSame($calledListeners, [
            [BeforeEmptyTrashEventInterface::class, 10],
            [BeforeEmptyTrashEventInterface::class, 0],
            [EmptyTrashEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testEmptyTrashStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeEmptyTrashEventInterface::class,
            EmptyTrashEventInterface::class
        );

        $parameters = [
        ];

        $resultList = $this->createMock(TrashItemDeleteResultList::class);
        $eventResultList = $this->createMock(TrashItemDeleteResultList::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('emptyTrash')->willReturn($resultList);

        $traceableEventDispatcher->addListener(BeforeEmptyTrashEventInterface::class, function (BeforeEmptyTrashEventInterface $event) use ($eventResultList) {
            $event->setResultList($eventResultList);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->emptyTrash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventResultList, $result);
        $this->assertSame($calledListeners, [
            [BeforeEmptyTrashEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeEmptyTrashEventInterface::class, 0],
            [EmptyTrashEventInterface::class, 0],
        ]);
    }

    public function testTrashEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeTrashEventInterface::class,
            TrashEventInterface::class
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
            [BeforeTrashEventInterface::class, 0],
            [TrashEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnTrashResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeTrashEventInterface::class,
            TrashEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $trashItem = $this->createMock(TrashItem::class);
        $eventTrashItem = $this->createMock(TrashItem::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('trash')->willReturn($trashItem);

        $traceableEventDispatcher->addListener(BeforeTrashEventInterface::class, function (BeforeTrashEventInterface $event) use ($eventTrashItem) {
            $event->setResult($eventTrashItem);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->trash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventTrashItem, $result);
        $this->assertSame($calledListeners, [
            [BeforeTrashEventInterface::class, 10],
            [BeforeTrashEventInterface::class, 0],
            [TrashEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testTrashStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeTrashEventInterface::class,
            TrashEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $trashItem = $this->createMock(TrashItem::class);
        $eventTrashItem = $this->createMock(TrashItem::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('trash')->willReturn($trashItem);

        $traceableEventDispatcher->addListener(BeforeTrashEventInterface::class, function (BeforeTrashEventInterface $event) use ($eventTrashItem) {
            $event->setResult($eventTrashItem);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->trash(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventTrashItem, $result);
        $this->assertSame($calledListeners, [
            [BeforeTrashEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeTrashEventInterface::class, 0],
            [TrashEventInterface::class, 0],
        ]);
    }

    public function testRecoverEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRecoverEventInterface::class,
            RecoverEventInterface::class
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
            [BeforeRecoverEventInterface::class, 0],
            [RecoverEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnRecoverResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRecoverEventInterface::class,
            RecoverEventInterface::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('recover')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeRecoverEventInterface::class, function (BeforeRecoverEventInterface $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->recover(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeRecoverEventInterface::class, 10],
            [BeforeRecoverEventInterface::class, 0],
            [RecoverEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRecoverStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRecoverEventInterface::class,
            RecoverEventInterface::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('recover')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeRecoverEventInterface::class, function (BeforeRecoverEventInterface $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->recover(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeRecoverEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRecoverEventInterface::class, 0],
            [RecoverEventInterface::class, 0],
        ]);
    }

    public function testDeleteTrashItemEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTrashItemEventInterface::class,
            DeleteTrashItemEventInterface::class
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
            [BeforeDeleteTrashItemEventInterface::class, 0],
            [DeleteTrashItemEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteTrashItemResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTrashItemEventInterface::class,
            DeleteTrashItemEventInterface::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
        ];

        $result = $this->createMock(TrashItemDeleteResult::class);
        $eventResult = $this->createMock(TrashItemDeleteResult::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('deleteTrashItem')->willReturn($result);

        $traceableEventDispatcher->addListener(BeforeDeleteTrashItemEventInterface::class, function (BeforeDeleteTrashItemEventInterface $event) use ($eventResult) {
            $event->setResult($eventResult);
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteTrashItem(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventResult, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteTrashItemEventInterface::class, 10],
            [BeforeDeleteTrashItemEventInterface::class, 0],
            [DeleteTrashItemEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteTrashItemStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteTrashItemEventInterface::class,
            DeleteTrashItemEventInterface::class
        );

        $parameters = [
            $this->createMock(TrashItem::class),
        ];

        $result = $this->createMock(TrashItemDeleteResult::class);
        $eventResult = $this->createMock(TrashItemDeleteResult::class);
        $innerServiceMock = $this->createMock(TrashServiceInterface::class);
        $innerServiceMock->method('deleteTrashItem')->willReturn($result);

        $traceableEventDispatcher->addListener(BeforeDeleteTrashItemEventInterface::class, function (BeforeDeleteTrashItemEventInterface $event) use ($eventResult) {
            $event->setResult($eventResult);
            $event->stopPropagation();
        }, 10);

        $service = new TrashService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteTrashItem(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventResult, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteTrashItemEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteTrashItemEventInterface::class, 0],
            [DeleteTrashItemEventInterface::class, 0],
        ]);
    }
}
