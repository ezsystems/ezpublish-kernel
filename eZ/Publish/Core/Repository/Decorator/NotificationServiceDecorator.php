<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\NotificationService;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\API\Repository\Values\Notification\NotificationList;

abstract class NotificationServiceDecorator implements NotificationService
{
    /**
     * @var \eZ\Publish\API\Repository\NotificationService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\NotificationService $service
     */
    public function __construct(NotificationService $service)
    {
        $this->service = $service;
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
    public function createNotification(CreateStruct $createStruct): Notification
    {
        return $this->service->createNotification($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteNotification(Notification $notification): void
    {
        $this->service->deleteNotification($notification);
    }
}
