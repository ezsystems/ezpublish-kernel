<?php
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\Values\Notification\CreateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification;
use eZ\Publish\API\Repository\Values\Notification\NotificationList;

class NotificationService implements NotificationServiceInterface
{
    /** @var \eZ\Publish\API\Repository\NotificationService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\NotificationService $service
     */
    public function __construct(
        NotificationServiceInterface $service
    ) {
        $this->service = $service;
    }

    /**
     * Get currently logged user notifications.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Notification\NotificationList
     */
    public function loadNotifications(int $offset, int $limit): NotificationList
    {
        return $this->service->loadNotifications($offset, $limit);
    }

    /**
     * @param int $notificationId
     *
     * @return \eZ\Publish\API\Repository\Values\Notification\Notification
     */
    public function getNotification(int $notificationId): Notification
    {
        return $this->service->getNotification($notificationId);
    }

    /**
     * Mark notification as read so it no longer bother the user.
     *
     * @param \eZ\Publish\API\Repository\Values\Notification\Notification $notification
     */
    public function markNotificationAsRead(Notification $notification): void
    {
        $this->service->markNotificationAsRead($notification);
    }

    /**
     * Get count of unread users notifications.
     *
     * @return int
     */
    public function getPendingNotificationCount(): int
    {
        return $this->service->getPendingNotificationCount();
    }

    /**
     * Get count of total users notifications.
     *
     * @return int
     */
    public function getNotificationCount(): int
    {
        return $this->service->getNotificationCount();
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Notification\Notification $notification
     */
    public function deleteNotification(Notification $notification): void
    {
        $this->service->deleteNotification($notification);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Notification\CreateStruct $createStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Notification\Notification
     */
    public function createNotification(CreateStruct $createStruct): Notification
    {
        return $this->service->createNotification($createStruct);
    }
}
