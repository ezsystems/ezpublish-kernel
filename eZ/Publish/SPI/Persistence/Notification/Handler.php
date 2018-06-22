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
     * @param int $notificationId
     * @param \eZ\Publish\SPI\Persistence\Notification\UpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function updateNotification(int $notificationId, UpdateStruct $updateStruct): Notification;

    /**
     * Count users unread Notifications.
     *
     * @param int $ownerId
     *
     * @return int
     */
    public function countPendingNotifications(int $ownerId): int;

    /**
     * Get Notification by its id.
     *
     * @param int $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotificationById(int $notificationId): Notification;

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function loadUserNotifications(int $userId, int $offset, int $limit): array;

    /**
     * @param int $currentUserId
     *
     * @return int
     */
    public function countNotifications(int $currentUserId): int;
}
