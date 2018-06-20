<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

interface NotificationService
{
    /**
     * Get currently logged user notifications.
     *
     * @param int $limit Number of notifications to get
     * @param int $page Notifications pagination
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function getUserNotifications(int $limit, int $page): array;

    /**
     * Mark notification as read so it no longer bother the user.
     *
     * @param mixed $notificationId Notification id to be marked as read
     */
    public function markNotificationAsRead($notificationId);

    /**
     * Get count of unread users notifications.
     *
     * @return int
     */
    public function getUserPendingNotificationCount();

    /**
     * Get total count of users notifications.
     *
     * @return int
     */
    public function getUserNotificationCount();

    /**
     * @param mixed $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotification($notificationId);
}
