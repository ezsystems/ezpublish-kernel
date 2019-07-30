<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\Notification\BeforeCreateNotificationEvent as BeforeCreateNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\BeforeDeleteNotificationEvent as BeforeDeleteNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\BeforeMarkNotificationAsReadEvent as BeforeMarkNotificationAsReadEventInterface;
use eZ\Publish\API\Repository\Events\Notification\CreateNotificationEvent as CreateNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\DeleteNotificationEvent as DeleteNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\MarkNotificationAsReadEvent as MarkNotificationAsReadEventInterface;
use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\Core\Event\NotificationService;

class NotificationServiceTest extends AbstractServiceTest
{
    public function testCreateNotificationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateNotificationEventInterface::class,
            CreateNotificationEventInterface::class
        );

        $parameters = [
            $this->createMock(CreateStruct::class),
        ];

        $notification = $this->createMock(Notification::class);
        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);
        $innerServiceMock->method('createNotification')->willReturn($notification);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($notification, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateNotificationEventInterface::class, 0],
            [CreateNotificationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateNotificationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateNotificationEventInterface::class,
            CreateNotificationEventInterface::class
        );

        $parameters = [
            $this->createMock(CreateStruct::class),
        ];

        $notification = $this->createMock(Notification::class);
        $eventNotification = $this->createMock(Notification::class);
        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);
        $innerServiceMock->method('createNotification')->willReturn($notification);

        $traceableEventDispatcher->addListener(BeforeCreateNotificationEventInterface::class, function (BeforeCreateNotificationEventInterface $event) use ($eventNotification) {
            $event->setNotification($eventNotification);
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventNotification, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateNotificationEventInterface::class, 10],
            [BeforeCreateNotificationEventInterface::class, 0],
            [CreateNotificationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateNotificationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateNotificationEventInterface::class,
            CreateNotificationEventInterface::class
        );

        $parameters = [
            $this->createMock(CreateStruct::class),
        ];

        $notification = $this->createMock(Notification::class);
        $eventNotification = $this->createMock(Notification::class);
        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);
        $innerServiceMock->method('createNotification')->willReturn($notification);

        $traceableEventDispatcher->addListener(BeforeCreateNotificationEventInterface::class, function (BeforeCreateNotificationEventInterface $event) use ($eventNotification) {
            $event->setNotification($eventNotification);
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventNotification, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateNotificationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateNotificationEventInterface::class, 0],
            [CreateNotificationEventInterface::class, 0],
        ]);
    }

    public function testDeleteNotificationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteNotificationEventInterface::class,
            DeleteNotificationEventInterface::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteNotificationEventInterface::class, 0],
            [DeleteNotificationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteNotificationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteNotificationEventInterface::class,
            DeleteNotificationEventInterface::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteNotificationEventInterface::class, function (BeforeDeleteNotificationEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteNotificationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteNotificationEventInterface::class, 0],
            [DeleteNotificationEventInterface::class, 0],
        ]);
    }

    public function testMarkNotificationAsReadEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMarkNotificationAsReadEventInterface::class,
            MarkNotificationAsReadEventInterface::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->markNotificationAsRead(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMarkNotificationAsReadEventInterface::class, 0],
            [MarkNotificationAsReadEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMarkNotificationAsReadStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMarkNotificationAsReadEventInterface::class,
            MarkNotificationAsReadEventInterface::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeMarkNotificationAsReadEventInterface::class, function (BeforeMarkNotificationAsReadEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->markNotificationAsRead(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMarkNotificationAsReadEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeMarkNotificationAsReadEventInterface::class, 0],
            [MarkNotificationAsReadEventInterface::class, 0],
        ]);
    }
}
