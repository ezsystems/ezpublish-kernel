<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification as APINotification;

/**
 * SPI cache for Notification Handler.
 *
 * @see \eZ\Publish\SPI\Persistence\Notification\Handler
 */
class NotificationHandler extends AbstractHandler implements Handler
{
    /**
     * {@inheritdoc}
     */
    public function createNotification(CreateStruct $createStruct): Notification
    {
        $this->logger->logCall(__METHOD__, [
            'createStruct' => $createStruct,
        ]);

        $this->cache->invalidateTags([
            'notification-count-' . $createStruct->ownerId,
            'notification-pending-count-' . $createStruct->ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->createNotification($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateNotification(APINotification $notification, UpdateStruct $updateStruct): Notification
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        $this->cache->invalidateTags([
            'notification-' . $notification->id,
            'notification-pending-count-' . $notification->ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->updateNotification($notification, $updateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(APINotification $notification): void
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        $this->cache->invalidateTags([
            'notification-' . $notification->id,
            'notification-count-' . $notification->ownerId,
            'notification-pending-count-' . $notification->ownerId,
        ]);

        $this->persistenceHandler->notificationHandler()->delete($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingNotifications(int $ownerId): int
    {
        $cacheItem = $this->cache->getItem('ez-notification-pending-count-' . $ownerId);

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['ownerId' => $ownerId]);

            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countPendingNotifications($ownerId);

        $cacheItem->set($count);
        $cacheItem->tag(['notification-pending-count-' . $ownerId]);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countNotifications(int $ownerId): int
    {
        $cacheItem = $this->cache->getItem('ez-notification-count-' . $ownerId);

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['ownerId' => $ownerId]);

            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countNotifications($ownerId);

        $cacheItem->set($count);
        $cacheItem->tag(['notification-count-' . $ownerId]);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationById(int $notificationId): Notification
    {
        $cacheItem = $this->cache->getItem('ez-notification-' . $notificationId);

        $notification = $cacheItem->get();
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(__METHOD__, ['notificationId' => $notificationId]);

            return $notification;
        }

        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notificationId,
        ]);

        $notification = $this->persistenceHandler->notificationHandler()->getNotificationById($notificationId);

        $cacheItem->set($notification);
        $cacheItem->tag(['notification-' . $notificationId]);
        $this->cache->save($cacheItem);

        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserNotifications(int $userId, int $offset, int $limit): array
    {
        $this->logger->logCall(__METHOD__, [
            'ownerId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
        ]);

        return $this->persistenceHandler->notificationHandler()->loadUserNotifications($userId, $offset, $limit);
    }
}
