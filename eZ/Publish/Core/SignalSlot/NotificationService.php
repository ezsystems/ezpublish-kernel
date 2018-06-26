<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\API\Repository\Values\Notification\NotificationList;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationDeleteSignal;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationCreateSignal;
use eZ\Publish\Core\SignalSlot\Signal\NotificationService\NotificationReadSignal;

class NotificationService implements NotificationServiceInterface
{
    /** @var \eZ\Publish\API\Repository\NotificationService */
    protected $service;

    /** @var \eZ\Publish\Core\SignalSlot\SignalDispatcher */
    protected $signalDispatcher;

    /**
     * @param \eZ\Publish\API\Repository\NotificationService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(NotificationServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function loadNotifications(int $offset, int $limit): NotificationList
    {
        return $this->service->loadNotifications($offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotification(int $notificationId): Notification
    {
        return $this->service->getNotification($notificationId);
    }

    /**
     * {@inheritdoc}
     */
    public function markNotificationAsRead(Notification $notification): void
    {
        $this->signalDispatcher->emit(new NotificationReadSignal([
            'notificationId' => $notification->id,
        ]));

        $this->service->markNotificationAsRead($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingNotificationCount(): int
    {
        return $this->service->getPendingNotificationCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationCount(): int
    {
        return $this->service->getNotificationCount();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteNotification(Notification $notification): void
    {
        $this->signalDispatcher->emit(new NotificationDeleteSignal([
            'notificationId' => $notification->id,
        ]));

        $this->service->deleteNotification($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function createNotification(CreateStruct $createStruct): Notification
    {
        $this->signalDispatcher->emit(new NotificationCreateSignal([
            'ownerId' => $createStruct->ownerId,
            'type' => $createStruct->type,
            'data' => $createStruct->data,
        ]));

        return $this->service->createNotification($createStruct);
    }
}
