<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;

class NotificationService implements NotificationServiceInterface
{
    /** @var \eZ\Publish\SPI\Persistence\Notification\Handler $persistenceHandler */
    protected $persistenceHandler;

    /** @var \eZ\Publish\API\Repository\Repository $kernelRepository */
    protected $kernelRepository;

    /**
     * @param \eZ\Publish\SPI\Persistence\Notification\Handler $persistenceHandler
     * @param \eZ\Publish\API\Repository\Repository $kernelRepository
     */
    public function __construct(Handler $persistenceHandler, Repository $kernelRepository)
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->kernelRepository = $kernelRepository;
    }

    /**
     * Get currently logged user notifications.
     *
     * @param int $limit Number of notifications to get
     * @param int $page Notifications pagination
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function getUserNotifications(int $limit, int $page): array
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        return $this->persistenceHandler->getNotificationsByOwnerId($currentUser->id, $limit, $page);
    }

    /**
     * Mark notification as read so it no longer bother the user.
     *
     * @param mixed $notificationId Notification id to be marked as read
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function markNotificationAsRead($notificationId)
    {
        $currentUser = $this->kernelRepository->getCurrentUser();
        $notification = $this->persistenceHandler->getNotificationById($notificationId);

        if (!$notification->id) {
            throw new NotFoundException('Notification', $notificationId);
        }

        if ($notification->ownerId !== $currentUser->id) {
            throw new UnauthorizedException($notificationId, 'Notification');
        }

        if (!$notification->isPending) {
            return;
        }

        $notification->isPending = false;

        $this->persistenceHandler->updateNotification($notification);
    }

    /**
     * Get count of unread users notifications.
     *
     * @return int
     */
    public function getUserPendingNotificationCount(): int
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        return $this->persistenceHandler->countPendingNotificationsByOwnerId($currentUser->id);
    }

    /**
     * Get total count of users notifications.
     *
     * @return int
     */
    public function getUserNotificationCount(): int
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        return $this->persistenceHandler->countNotificationsByOwnerId($currentUser->id);
    }

    /**
     * @param mixed $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getNotification($notificationId): Notification
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        $notification = $this->persistenceHandler->getNotificationById($notificationId);

        if (!$notification->ownerId || $currentUser->id != $notification->ownerId) {
            throw new NotFoundException('Notification', $notificationId);
        }

        return $notification;
    }
}
