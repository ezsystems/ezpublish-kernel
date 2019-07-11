<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\Core\Event\Notification\BeforeCreateNotificationEvent;
use eZ\Publish\Core\Event\Notification\BeforeDeleteNotificationEvent;
use eZ\Publish\Core\Event\Notification\BeforeMarkNotificationAsReadEvent;
use eZ\Publish\Core\Event\Notification\CreateNotificationEvent;
use eZ\Publish\Core\Event\Notification\DeleteNotificationEvent;
use eZ\Publish\Core\Event\Notification\MarkNotificationAsReadEvent;
use eZ\Publish\Core\Event\NotificationService;

class NotificationServiceTest extends AbstractServiceTest
{
    public function testCreateNotificationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateNotificationEvent::class,
            CreateNotificationEvent::class
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
            [BeforeCreateNotificationEvent::class, 0],
            [CreateNotificationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateNotificationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateNotificationEvent::class,
            CreateNotificationEvent::class
        );

        $parameters = [
            $this->createMock(CreateStruct::class),
        ];

        $notification = $this->createMock(Notification::class);
        $eventNotification = $this->createMock(Notification::class);
        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);
        $innerServiceMock->method('createNotification')->willReturn($notification);

        $traceableEventDispatcher->addListener(BeforeCreateNotificationEvent::class, function (\eZ\Publish\API\Repository\Events\Notification\BeforeCreateNotificationEvent $event) use ($eventNotification) {
            $event->setNotification($eventNotification);
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventNotification, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateNotificationEvent::class, 10],
            [BeforeCreateNotificationEvent::class, 0],
            [CreateNotificationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateNotificationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateNotificationEvent::class,
            CreateNotificationEvent::class
        );

        $parameters = [
            $this->createMock(CreateStruct::class),
        ];

        $notification = $this->createMock(Notification::class);
        $eventNotification = $this->createMock(Notification::class);
        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);
        $innerServiceMock->method('createNotification')->willReturn($notification);

        $traceableEventDispatcher->addListener(BeforeCreateNotificationEvent::class, function (\eZ\Publish\API\Repository\Events\Notification\BeforeCreateNotificationEvent $event) use ($eventNotification) {
            $event->setNotification($eventNotification);
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventNotification, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateNotificationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateNotificationEvent::class, 0],
            [CreateNotificationEvent::class, 0],
        ]);
    }

    public function testDeleteNotificationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteNotificationEvent::class,
            DeleteNotificationEvent::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteNotificationEvent::class, 0],
            [DeleteNotificationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteNotificationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteNotificationEvent::class,
            DeleteNotificationEvent::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteNotificationEvent::class, function (\eZ\Publish\API\Repository\Events\Notification\BeforeDeleteNotificationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteNotificationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteNotificationEvent::class, 0],
            [DeleteNotificationEvent::class, 0],
        ]);
    }

    public function testMarkNotificationAsReadEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMarkNotificationAsReadEvent::class,
            MarkNotificationAsReadEvent::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->markNotificationAsRead(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMarkNotificationAsReadEvent::class, 0],
            [MarkNotificationAsReadEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMarkNotificationAsReadStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMarkNotificationAsReadEvent::class,
            MarkNotificationAsReadEvent::class
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeMarkNotificationAsReadEvent::class, function (\eZ\Publish\API\Repository\Events\Notification\BeforeMarkNotificationAsReadEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->markNotificationAsRead(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMarkNotificationAsReadEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeMarkNotificationAsReadEvent::class, 0],
            [MarkNotificationAsReadEvent::class, 0],
        ]);
    }
}
