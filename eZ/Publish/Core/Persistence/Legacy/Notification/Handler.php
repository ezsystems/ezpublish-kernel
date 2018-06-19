<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Notification;

use eZ\Publish\SPI\Persistence\Notification\Handler as HandlerInterface;
use eZ\Publish\SPI\Persistence\Notification\Notification;

class Handler implements HandlerInterface
{
    protected $gateway;

    /**
     * Handler constructor.
     *
     * @param HandlerInterface $gateway
     */
    public function __construct(HandlerInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param Notification $notification
     *
     * @return int
     */
    public function createNotification(Notification $notification)
    {
        return $this->gateway->createNotification($notification);
    }

    /**
     * Get paginated users Notifications.
     *
     * @param int $ownerId
     * @param int $limit
     * @param int $page
     *
     * @return Notification[]
     */
    public function getNotificationsByOwnerId($ownerId, $limit = 100, $page = 0)
    {
        return $this->gateway->getNotificationsByOwnerId($ownerId, $limit, $page);
    }

    /**
     * Count users unread Notifications.
     *
     * @param int $ownerId
     *
     * @return int
     */
    public function countPendingNotificationsByOwnerId($ownerId)
    {
        return $this->gateway->countPendingNotificationsByOwnerId($ownerId);
    }

    /**
     * Get Notification by its id.
     *
     * @param int $notificationId
     *
     * @return Notification
     */
    public function getNotificationById($notificationId)
    {
        return $this->gateway->getNotificationById($notificationId);
    }

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
    public function updateNotification(Notification $notification)
    {
        return $this->gateway->updateNotification($notification);
    }

    /**
     * Count total users Notifications.
     *
     * @param int $ownerId
     *
     * @return int
     */
    public function countNotificationsByOwnerId($ownerId)
    {
        return $this->gateway->countNotificationsByOwnerId($ownerId);
    }
}
