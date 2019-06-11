<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\Core\Event\NotificationService;
use eZ\Publish\Core\Event\Notification\BeforeCreateNotificationEvent;
use eZ\Publish\Core\Event\Notification\BeforeDeleteNotificationEvent;
use eZ\Publish\Core\Event\Notification\BeforeMarkNotificationAsReadEvent;
use eZ\Publish\Core\Event\Notification\NotificationEvents;

class NotificationServiceTest extends AbstractServiceTest
{
    public function testCreateNotificationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            NotificationEvents::BEFORE_CREATE_NOTIFICATION,
            NotificationEvents::CREATE_NOTIFICATION
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
            [NotificationEvents::BEFORE_CREATE_NOTIFICATION, 0],
            [NotificationEvents::CREATE_NOTIFICATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateNotificationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            NotificationEvents::BEFORE_CREATE_NOTIFICATION,
            NotificationEvents::CREATE_NOTIFICATION
        );

        $parameters = [
            $this->createMock(CreateStruct::class),
        ];

        $notification = $this->createMock(Notification::class);
        $eventNotification = $this->createMock(Notification::class);
        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);
        $innerServiceMock->method('createNotification')->willReturn($notification);

        $traceableEventDispatcher->addListener(NotificationEvents::BEFORE_CREATE_NOTIFICATION, function (BeforeCreateNotificationEvent $event) use ($eventNotification) {
            $event->setNotification($eventNotification);
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventNotification, $result);
        $this->assertSame($calledListeners, [
            [NotificationEvents::BEFORE_CREATE_NOTIFICATION, 10],
            [NotificationEvents::BEFORE_CREATE_NOTIFICATION, 0],
            [NotificationEvents::CREATE_NOTIFICATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateNotificationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            NotificationEvents::BEFORE_CREATE_NOTIFICATION,
            NotificationEvents::CREATE_NOTIFICATION
        );

        $parameters = [
            $this->createMock(CreateStruct::class),
        ];

        $notification = $this->createMock(Notification::class);
        $eventNotification = $this->createMock(Notification::class);
        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);
        $innerServiceMock->method('createNotification')->willReturn($notification);

        $traceableEventDispatcher->addListener(NotificationEvents::BEFORE_CREATE_NOTIFICATION, function (BeforeCreateNotificationEvent $event) use ($eventNotification) {
            $event->setNotification($eventNotification);
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventNotification, $result);
        $this->assertSame($calledListeners, [
            [NotificationEvents::BEFORE_CREATE_NOTIFICATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [NotificationEvents::CREATE_NOTIFICATION, 0],
            [NotificationEvents::BEFORE_CREATE_NOTIFICATION, 0],
        ]);
    }

    public function testDeleteNotificationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            NotificationEvents::BEFORE_DELETE_NOTIFICATION,
            NotificationEvents::DELETE_NOTIFICATION
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [NotificationEvents::BEFORE_DELETE_NOTIFICATION, 0],
            [NotificationEvents::DELETE_NOTIFICATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteNotificationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            NotificationEvents::BEFORE_DELETE_NOTIFICATION,
            NotificationEvents::DELETE_NOTIFICATION
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $traceableEventDispatcher->addListener(NotificationEvents::BEFORE_DELETE_NOTIFICATION, function (BeforeDeleteNotificationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteNotification(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [NotificationEvents::BEFORE_DELETE_NOTIFICATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [NotificationEvents::DELETE_NOTIFICATION, 0],
            [NotificationEvents::BEFORE_DELETE_NOTIFICATION, 0],
        ]);
    }

    public function testMarkNotificationAsReadEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            NotificationEvents::BEFORE_MARK_NOTIFICATION_AS_READ,
            NotificationEvents::MARK_NOTIFICATION_AS_READ
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->markNotificationAsRead(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [NotificationEvents::BEFORE_MARK_NOTIFICATION_AS_READ, 0],
            [NotificationEvents::MARK_NOTIFICATION_AS_READ, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMarkNotificationAsReadStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            NotificationEvents::BEFORE_MARK_NOTIFICATION_AS_READ,
            NotificationEvents::MARK_NOTIFICATION_AS_READ
        );

        $parameters = [
            $this->createMock(Notification::class),
        ];

        $innerServiceMock = $this->createMock(NotificationServiceInterface::class);

        $traceableEventDispatcher->addListener(NotificationEvents::BEFORE_MARK_NOTIFICATION_AS_READ, function (BeforeMarkNotificationAsReadEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new NotificationService($innerServiceMock, $traceableEventDispatcher);
        $service->markNotificationAsRead(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [NotificationEvents::BEFORE_MARK_NOTIFICATION_AS_READ, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [NotificationEvents::MARK_NOTIFICATION_AS_READ, 0],
            [NotificationEvents::BEFORE_MARK_NOTIFICATION_AS_READ, 0],
        ]);
    }
}
