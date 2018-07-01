<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use DateTime;
use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct as APICreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification as APINotification;
use eZ\Publish\API\Repository\Values\Notification\NotificationList;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;

class NotificationService implements NotificationServiceInterface
{
    /** @var \eZ\Publish\SPI\Persistence\Notification\Handler */
    protected $persistenceHandler;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    protected $permissionResolver;

    /**
     * @param \eZ\Publish\SPI\Persistence\Notification\Handler $persistenceHandler
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(Handler $persistenceHandler, PermissionResolver $permissionResolver)
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function loadNotifications(int $offset = 0, int $limit = 25): NotificationList
    {
        $currentUserId = $this->getCurrentUserId();

        $list = new NotificationList();
        $list->totalCount = $this->persistenceHandler->countNotifications($currentUserId);
        if ($list->totalCount > 0) {
            $list->items = array_map(function (Notification $spiNotification) {
                return $this->buildDomainObject($spiNotification);
            }, $this->persistenceHandler->loadUserNotifications($currentUserId, $offset, $limit));
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function createNotification(APICreateStruct $createStruct): APINotification
    {
        $spiCreateStruct = new CreateStruct();

        if (empty($createStruct->ownerId)) {
            throw new InvalidArgumentException('ownerId', $createStruct->ownerId);
        }

        $spiCreateStruct->ownerId = $createStruct->ownerId;

        if (empty($createStruct->type)) {
            throw new InvalidArgumentException('type', $createStruct->type);
        }

        $spiCreateStruct->type = $createStruct->type;
        $spiCreateStruct->isPending = (bool) $createStruct->isPending;
        $spiCreateStruct->data = $createStruct->data;
        $spiCreateStruct->created = (new DateTime())->getTimestamp();

        return $this->buildDomainObject(
            $this->persistenceHandler->createNotification($spiCreateStruct)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNotification(int $notificationId): APINotification
    {
        $notification = $this->persistenceHandler->getNotificationById($notificationId);

        $currentUserId = $this->getCurrentUserId();
        if (!$notification->ownerId || $currentUserId != $notification->ownerId) {
            throw new NotFoundException('Notification', $notificationId);
        }

        return $this->buildDomainObject($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function markNotificationAsRead(APINotification $notification): void
    {
        $currentUserId = $this->getCurrentUserId();

        if (!$notification->id) {
            throw new NotFoundException('Notification', $notification->id);
        }

        if ($notification->ownerId !== $currentUserId) {
            throw new UnauthorizedException($notification->id, 'Notification');
        }

        if (!$notification->isPending) {
            return;
        }

        $updateStruct = new UpdateStruct();
        $updateStruct->isPending = false;

        $this->persistenceHandler->updateNotification($notification, $updateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingNotificationCount(): int
    {
        return $this->persistenceHandler->countPendingNotifications(
            $this->getCurrentUserId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationCount(): int
    {
        return $this->persistenceHandler->countNotifications(
            $this->getCurrentUserId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteNotification(APINotification $notification): void
    {
        $this->persistenceHandler->delete($notification);
    }

    /**
     * Builds Notification domain object from ValueObject returned by Persistence API.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $spiNotification
     *
     * @return \eZ\Publish\API\Repository\Values\Notification\Notification
     */
    protected function buildDomainObject(Notification $spiNotification): APINotification
    {
        return new APINotification([
            'id' => $spiNotification->id,
            'ownerId' => $spiNotification->ownerId,
            'isPending' => $spiNotification->isPending,
            'type' => $spiNotification->type,
            'created' => new DateTime("@{$spiNotification->created}"),
            'data' => $spiNotification->data,
        ]);
    }

    private function getCurrentUserId(): int
    {
        return $this->permissionResolver
            ->getCurrentUserReference()
            ->getUserId();
    }
}
