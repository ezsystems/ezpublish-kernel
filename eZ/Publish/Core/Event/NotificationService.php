<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\NotificationServiceDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\Core\Event\Notification\BeforeCreateNotificationEvent;
use eZ\Publish\Core\Event\Notification\BeforeDeleteNotificationEvent;
use eZ\Publish\Core\Event\Notification\BeforeMarkNotificationAsReadEvent;
use eZ\Publish\Core\Event\Notification\CreateNotificationEvent;
use eZ\Publish\Core\Event\Notification\DeleteNotificationEvent;
use eZ\Publish\Core\Event\Notification\MarkNotificationAsReadEvent;
use eZ\Publish\Core\Event\Notification\NotificationEvents;

class NotificationService extends NotificationServiceDecorator
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        NotificationServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function markNotificationAsRead(Notification $notification): void
    {
        $eventData = [$notification];

        $beforeEvent = new BeforeMarkNotificationAsReadEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(NotificationEvents::BEFORE_MARK_NOTIFICATION_AS_READ, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::markNotificationAsRead($notification);

        $this->eventDispatcher->dispatch(
            NotificationEvents::MARK_NOTIFICATION_AS_READ,
            new MarkNotificationAsReadEvent(...$eventData)
        );
    }

    public function createNotification(CreateStruct $createStruct): Notification
    {
        $eventData = [$createStruct];

        $beforeEvent = new BeforeCreateNotificationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(NotificationEvents::BEFORE_CREATE_NOTIFICATION, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getNotification();
        }

        $notification = $beforeEvent->hasNotification()
            ? $beforeEvent->getNotification()
            : parent::createNotification($createStruct);

        $this->eventDispatcher->dispatch(
            NotificationEvents::CREATE_NOTIFICATION,
            new CreateNotificationEvent($notification, ...$eventData)
        );

        return $notification;
    }

    public function deleteNotification(Notification $notification): void
    {
        $eventData = [$notification];

        $beforeEvent = new BeforeDeleteNotificationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(NotificationEvents::BEFORE_DELETE_NOTIFICATION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteNotification($notification);

        $this->eventDispatcher->dispatch(
            NotificationEvents::DELETE_NOTIFICATION,
            new DeleteNotificationEvent(...$eventData)
        );
    }
}
