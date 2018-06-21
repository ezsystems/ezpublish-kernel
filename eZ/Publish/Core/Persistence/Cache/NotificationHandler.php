<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;

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
    public function createNotification(Notification $notification)
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        $this->cache->invalidateTags([
            'notification-count-' . $notification->ownerId,
            'notification-pending-count-' . $notification->ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->createNotification($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function updateNotification(Notification $notification)
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        $this->cache->invalidateTags([
            'notification-' . $notification->id,
            'notification-pending-count-' . $notification->ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->updateNotification($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Notification $notification): void
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        $this->cache->invalidateTags([
            'notification-' . $notification->id,
            'notification-count-' . $notification->ownerId,
            'notification-pending-count-' . $notification->ownerId,
        ]);

        // @todo:
        // $this->persistenceHandler->notificationHandler()->delete($notification->id);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationsByOwnerId($ownerId, $limit, $page)
    {
        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
            'limit' => $limit,
            'page' => $page,
        ]);

        return $this->persistenceHandler->notificationHandler()->getNotificationsByOwnerId($ownerId, $limit, $page);
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingNotificationsByOwnerId($ownerId)
    {
        $cacheItem = $this->cache->getItem('ez-notification-pending-count-' . $ownerId);

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countPendingNotificationsByOwnerId($ownerId);

        $cacheItem->set($count);
        $cacheItem->tag(['notification-pending-count-' . $ownerId]);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countNotificationsByOwnerId($ownerId)
    {
        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $cacheItem = $this->cache->getItem('ez-notification-count-' . $ownerId);

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countNotificationsByOwnerId($ownerId);

        $cacheItem->set($count);
        $cacheItem->tag(['notification-count-' . $ownerId]);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationById($id)
    {
        $cacheItem = $this->cache->getItem('ez-notification-' . $id);

        $notification = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $notification;
        }

        $this->logger->logCall(__METHOD__, [
            'notificationId' => $id,
        ]);

        $notification = $this->persistenceHandler->notificationHandler()->getNotificationById($id);

        $cacheItem->set($notification);
        $cacheItem->tag(['notification-' . $id]);
        $this->cache->save($cacheItem);

        return $notification;
    }
}
