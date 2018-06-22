<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\API\Repository\Values\Notification\NotificationList;

interface NotificationService
{
    /**
     * Get currently logged user notifications.

     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Notification\NotificationList
     */
    public function loadNotifications(int $offset, int $limit): NotificationList;

    /**
     * @param int $notificationId
     *
     * @return \eZ\Publish\API\Repository\Values\Notification\Notification
     */
    public function getNotification(int $notificationId): Notification;

    /**
     * Mark notification as read so it no longer bother the user.
     *
     * @param \eZ\Publish\API\Repository\Values\Notification\Notification $notification
     */
    public function markNotificationAsRead(Notification $notification): void;

    /**
     * Get count of unread users notifications.
     *
     * @return int
     */
    public function getPendingNotificationCount(): int;

    /**
     * Get count of total users notifications.
     *
     * @return int
     */
    public function getNotificationCount(): int;
}
