<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\NotificationService;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\API\Repository\Values\Notification\NotificationList;

abstract class NotificationServiceDecorator implements NotificationService
{
    /** @var \eZ\Publish\API\Repository\NotificationService */
    protected $innerService;

    public function __construct(NotificationService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function loadNotifications(
        int $offset,
        int $limit
    ): NotificationList {
        return $this->innerService->loadNotifications($offset, $limit);
    }

    public function getNotification(int $notificationId): Notification
    {
        return $this->innerService->getNotification($notificationId);
    }

    public function markNotificationAsRead(Notification $notification): void
    {
        $this->innerService->markNotificationAsRead($notification);
    }

    public function getPendingNotificationCount(): int
    {
        return $this->innerService->getPendingNotificationCount();
    }

    public function getNotificationCount(): int
    {
        return $this->innerService->getNotificationCount();
    }

    public function createNotification(CreateStruct $createStruct): Notification
    {
        return $this->innerService->createNotification($createStruct);
    }

    public function deleteNotification(Notification $notification): void
    {
        $this->innerService->deleteNotification($notification);
    }
}
