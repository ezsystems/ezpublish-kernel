<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Notification;

interface Handler
{
    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return mixed
     */
    public function createNotification(Notification $notification);

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function updateNotification(Notification $notification): Notification;

    /**
     * Get paginated users Notifications.
     *
     * @param mixed $ownerId
     * @param int $limit
     * @param int $page
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function getNotificationsByOwnerId($ownerId, int $limit, int $page): array;

    /**
     * Count users unread Notifications.
     *
     * @param mixed $ownerId
     *
     * @return int
     */
    public function countPendingNotificationsByOwnerId($ownerId): int;

    /**
     * Count total users Notifications.
     *
     * @param mixed $ownerId
     *
     * @return int
     */
    public function countNotificationsByOwnerId($ownerId): int;

    /**
     * Get Notification by its id.
     *
     * @param mixed $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotificationById($notificationId): Notification;
}
