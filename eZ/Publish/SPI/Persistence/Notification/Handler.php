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
     * @param Notification $notification
     *
     * @return int
     */
    public function createNotification(Notification $notification);

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @todo
     *
     * @param Notification $notification
     *
     * @return Notification
     */
    public function updateNotification(Notification $notification);

    /**
     * Get paginated users Notifications.
     *
     * @param int $ownerId
     * @param int $limit
     * @param int $page
     *
     * @return Notification[]
     */
    public function getNotificationsByOwnerId($ownerId, $limit, $page);

    /**
     * Count users unread Notifications.
     *
     * @param int $ownerId
     *
     * @return int
     */
    public function countPendingNotificationsByOwnerId($ownerId);

    /**
     * Count total users Notifications.
     *
     * @param int $ownerId
     *
     * @return int
     */
    public function countNotificationsByOwnerId($ownerId);

    /**
     * Get Notification by its id.
     *
     * @param int $notificationId
     *
     * @return Notification
     */
    public function getNotificationById($notificationId);
}
