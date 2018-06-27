<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Notification;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Handler as HandlerInterface;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification as APINotification;

class Handler implements HandlerInterface
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway */
    protected $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper */
    protected $mapper;

    /**
     * @param \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function createNotification(CreateStruct $createStruct): Notification
    {
        $id = $this->gateway->insert($createStruct);

        return $this->getNotificationById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingNotifications(int $ownerId): int
    {
        return $this->gateway->countUserPendingNotifications($ownerId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getNotificationById(int $notificationId): Notification
    {
        $notification = $this->mapper->extractNotificationsFromRows(
            $this->gateway->getNotificationById($notificationId)
        );

        if (count($notification) < 1) {
            throw new NotFoundException('Notification', $notificationId);
        }

        return reset($notification);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
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
     * {@inheritdoc}
     */
    public function countNotifications(int $userId): int
    {
        return $this->gateway->countUserNotifications($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserNotifications(int $userId, int $offset, int $limit): array
    {
        return $this->mapper->extractNotificationsFromRows(
            $this->gateway->loadUserNotifications($userId, $offset, $limit)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(APINotification $notification): void
    {
        $this->gateway->delete($notification->id);
    }
}
