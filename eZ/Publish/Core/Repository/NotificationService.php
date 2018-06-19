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

class NotificationService implements NotificationServiceInterface
{
    /** @var Handler $persistenceHandler */
    protected $persistenceHandler;

    /** @var Repository $kernelRepository */
    protected $kernelRepository;

    /**
     * NotificationService constructor.
     *
     * @param Handler $persistenceHandler
     * @param Repository $kernelRepository
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
     * @return \EzSystems\Notification\SPI\Persistence\ValueObject\Notification[]
     */
    public function getUserNotifications($limit, $page)
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        $notifications = $this->persistenceHandler->getNotificationsByOwnerId($currentUser->id, $limit, $page);

        return $notifications;
    }

    /**
     * Mark notification as read so it no longer bother the user.
     *
     * @param int $notificationId Notification id to be marked as read
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function markNotificationAsRead($notificationId)
    {
        $currentUser = $this->kernelRepository->getCurrentUser();
        $notification = $this->persistenceHandler->getNotificationById($notificationId);

        if (!$notification->id) {
            throw new NotFoundException('Notification', $notificationId);
        }

        if ($notification->ownerId != $currentUser->id) {
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
    public function getUserPendingNotificationCount()
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        $notifications = $this->persistenceHandler->countPendingNotificationsByOwnerId($currentUser->id);

        return $notifications;
    }

    /**
     * Get total count of users notifications.
     *
     * @return int
     */
    public function getUserNotificationCount()
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        $notifications = $this->persistenceHandler->countNotificationsByOwnerId($currentUser->id);

        return $notifications;
    }

    /**
     * @param $notificationId
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function getNotification($notificationId)
    {
        $currentUser = $this->kernelRepository->getCurrentUser();

        $notification = $this->persistenceHandler->getNotificationById($notificationId);

        if (!$notification->ownerId || $currentUser->id != $notification->ownerId) {
            throw new NotFoundException('Notification', $notificationId);
        }

        return $notification;
    }
}
