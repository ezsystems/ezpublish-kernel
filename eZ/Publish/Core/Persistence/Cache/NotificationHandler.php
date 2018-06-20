<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;

class NotificationHandler extends AbstractHandler implements Handler
{
    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return int
     */
    public function createNotification(Notification $notification): int
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        return $this->persistenceHandler->notificationHandler()->createNotification($notification);
    }

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function updateNotification(Notification $notification): Notification
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        return $this->persistenceHandler->notificationHandler()->updateNotification($notification);
    }

    /**
     * Get paginated users Notifications.
     *
     * @param mixed $ownerId
     * @param int $limit
     * @param int $page
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function getNotificationsByOwnerId($ownerId, int $limit, int $page): array
    {
        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
            'limit' => $limit,
            'page' => $page,
        ]);

        return $this->persistenceHandler->notificationHandler()->getNotificationsByOwnerId($ownerId, $limit, $page);
    }

    /**
     * Count users unread Notifications.
     *
     * @param mixed $ownerId
     *
     * @return int
     */
    public function countPendingNotificationsByOwnerId($ownerId): int
    {
        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->countPendingNotificationsByOwnerId($ownerId);
    }

    /**
     * Count total users Notifications.
     *
     * @param mixed $ownerId
     *
     * @return int
     */
    public function countNotificationsByOwnerId($ownerId): int
    {
        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->countNotificationsByOwnerId($ownerId);
    }

    /**
     * Get Notification by its id.
     *
     * @param int $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotificationById($notificationId): Notification
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notificationId,
        ]);

        return $this->persistenceHandler->notificationHandler()->getNotificationById($notificationId);
    }
}
