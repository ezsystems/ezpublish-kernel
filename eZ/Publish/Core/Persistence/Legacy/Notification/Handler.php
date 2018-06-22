<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Notification;

use eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase;
use eZ\Publish\SPI\Persistence\Notification\Handler as HandlerInterface;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification as APINotification;

class Handler implements HandlerInterface
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase */
    protected $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper */
    protected $mapper;

    /**
     * @param \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway\DoctrineDatabase $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper $mapper
     */
    public function __construct(DoctrineDatabase $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function createNotification(Notification $notification): Notification
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
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function getNotificationsByOwnerId(int $ownerId, int $limit = 100, int $page = 0): array
    {
        return $this->gateway->loadUserNotifications($ownerId, $limit, $page);
    }

    /**
     * Count users unread Notifications.
     *
     * @param mixed $ownerId
     *
     * @return int
     */
    public function countPendingNotifications(int $ownerId): int
    {
        return $this->gateway->countUserPendingNotifications($ownerId);
    }

    /**
     * Get Notification by its id.
     *
     * @param mixed $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotificationById(int $notificationId): Notification
    {
        $notification = $this->mapper->extractNotificationsFromRows(
            $this->gateway->getNotificationById($notificationId)
        );
        return reset($notification);
    }

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @todo
     *
     * @param \eZ\Publish\API\Repository\Values\Notification\Notification $apiNotification
     * @param \eZ\Publish\SPI\Persistence\Notification\UpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function updateNotification(APINotification $apiNotification, UpdateStruct $updateStruct): Notification
    {
        $notification = $this->mapper->createNotificationFromUpdateStruct(
            $updateStruct
        );
        $notification->id = $apiNotification->id;

        $this->gateway->updateNotification($notification);

        return $this->getNotificationById($notification->id);
    }

    /**
     * @param int $userId
     *
     * @return int
     */
    public function countNotifications(int $userId): int
    {
        return $this->gateway->countUserNotifications($userId);
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
        return $this->mapper->extractNotificationsFromRows(
            $this->gateway->loadUserNotifications($userId, $offset, $limit)
        );
    }
}
