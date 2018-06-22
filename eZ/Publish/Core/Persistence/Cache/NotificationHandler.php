<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;

class NotificationHandler extends AbstractHandler implements Handler
{
    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return mixed
     */
    public function createNotification(Notification $notification)
    {
        return $this->persistenceHandler->notificationHandler()->createNotification($notification);
    }

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @param int $notificationId
     * @param \eZ\Publish\SPI\Persistence\Notification\UpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function updateNotification(int $notificationId, UpdateStruct $updateStruct): Notification
    {
        return $this->persistenceHandler->notificationHandler()->updateNotification($notificationId, $updateStruct);
    }

    /**
     * Count users unread Notifications.
     *
     * @param int $ownerId
     *
     * @return int
     */
    public function countPendingNotifications(int $ownerId): int
    {
        return $this->persistenceHandler->notificationHandler()->countPendingNotifications($ownerId);
    }

    /**
     * Get Notification by its id.
     *
     * @param int $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotificationById(int $notificationId): Notification
    {
        return $this->persistenceHandler->notificationHandler()->getNotificationById($notificationId);
    }

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function loadUserNotifications(int $userId, int $offset, int $limit): array
    {
        return $this->persistenceHandler->notificationHandler()->loadUserNotifications($userId, $offset, $limit);
    }

    /**
     * @param int $currentUserId
     *
     * @return int
     */
    public function countNotifications(int $currentUserId): int
    {
        return $this->persistenceHandler->notificationHandler()->countNotifications($currentUserId);
    }
}
