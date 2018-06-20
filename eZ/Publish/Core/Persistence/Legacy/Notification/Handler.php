<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Notification;

use eZ\Publish\SPI\Persistence\Notification\Handler as HandlerInterface;
use eZ\Publish\SPI\Persistence\Notification\Notification;

class Handler implements HandlerInterface
{
    /** @var \eZ\Publish\SPI\Persistence\Notification\Handler */
    protected $gateway;

    /**
     * @param \eZ\Publish\SPI\Persistence\Notification\Handler $gateway
     */
    public function __construct(HandlerInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return mixed
     */
    public function createNotification(Notification $notification)
    {
        return $this->gateway->createNotification($notification);
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
    public function getNotificationsByOwnerId($ownerId, int $limit = 100, int $page = 0): array
    {
        return $this->gateway->getNotificationsByOwnerId($ownerId, $limit, $page);
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
        return $this->gateway->countPendingNotificationsByOwnerId($ownerId);
    }

    /**
     * Get Notification by its id.
     *
     * @param mixed $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotificationById($notificationId): Notification
    {
        return $this->gateway->getNotificationById($notificationId);
    }

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @todo
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function updateNotification(Notification $notification): Notification
    {
        return $this->gateway->updateNotification($notification);
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
        return $this->gateway->countNotificationsByOwnerId($ownerId);
    }
}
