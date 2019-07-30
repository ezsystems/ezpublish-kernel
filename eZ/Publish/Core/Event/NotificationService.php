<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\Events\Notification\BeforeCreateNotificationEvent as BeforeCreateNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\BeforeDeleteNotificationEvent as BeforeDeleteNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\BeforeMarkNotificationAsReadEvent as BeforeMarkNotificationAsReadEventInterface;
use eZ\Publish\API\Repository\Events\Notification\CreateNotificationEvent as CreateNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\DeleteNotificationEvent as DeleteNotificationEventInterface;
use eZ\Publish\API\Repository\Events\Notification\MarkNotificationAsReadEvent as MarkNotificationAsReadEventInterface;
use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\API\Repository\Events\Notification\BeforeCreateNotificationEvent;
use eZ\Publish\API\Repository\Events\Notification\BeforeDeleteNotificationEvent;
use eZ\Publish\API\Repository\Events\Notification\BeforeMarkNotificationAsReadEvent;
use eZ\Publish\API\Repository\Events\Notification\CreateNotificationEvent;
use eZ\Publish\API\Repository\Events\Notification\DeleteNotificationEvent;
use eZ\Publish\API\Repository\Events\Notification\MarkNotificationAsReadEvent;
use eZ\Publish\SPI\Repository\Decorator\NotificationServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NotificationService extends NotificationServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeMarkNotificationAsReadEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->markNotificationAsRead($notification);

        $this->eventDispatcher->dispatch(
            new MarkNotificationAsReadEvent(...$eventData),
            MarkNotificationAsReadEventInterface::class
        );
    }

    public function createNotification(CreateStruct $createStruct): Notification
    {
        $eventData = [$createStruct];

        $beforeEvent = new BeforeCreateNotificationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateNotificationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getNotification();
        }

        $notification = $beforeEvent->hasNotification()
            ? $beforeEvent->getNotification()
            : $this->innerService->createNotification($createStruct);

        $this->eventDispatcher->dispatch(
            new CreateNotificationEvent($notification, ...$eventData),
            CreateNotificationEventInterface::class
        );

        return $notification;
    }

    public function deleteNotification(Notification $notification): void
    {
        $eventData = [$notification];

        $beforeEvent = new BeforeDeleteNotificationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteNotificationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteNotification($notification);

        $this->eventDispatcher->dispatch(
            new DeleteNotificationEvent(...$eventData),
            DeleteNotificationEventInterface::class
        );
    }
}
