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
    private const NOTIFICATION_IDENTIFIER = 'notification';
    private const NOTIFICATION_COUNT_IDENTIFIER = 'notification_count';
    private const NOTIFICATION_PENDING_COUNT_IDENTIFIER = 'notification_pending_count';

    /**
     * {@inheritdoc}
     */
    public function createNotification(CreateStruct $createStruct): Notification
    {
        $this->logger->logCall(__METHOD__, [
            'createStruct' => $createStruct,
        ]);

        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_COUNT_IDENTIFIER, [$createStruct->ownerId], true),
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_PENDING_COUNT_IDENTIFIER, [$createStruct->ownerId], true),
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

        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_IDENTIFIER, [$notification->id], true),
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_PENDING_COUNT_IDENTIFIER, [$notification->ownerId], true),
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

        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_IDENTIFIER, [$notification->id], true),
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_COUNT_IDENTIFIER, [$notification->ownerId], true),
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_PENDING_COUNT_IDENTIFIER, [$notification->ownerId], true),
        ]);

        $this->persistenceHandler->notificationHandler()->delete($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingNotifications(int $ownerId): int
    {
        $cacheItem = $this->cache->getItem(
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_PENDING_COUNT_IDENTIFIER, [$ownerId], true)
        );

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countPendingNotifications($ownerId);

        $cacheItem->set($count);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countNotifications(int $ownerId): int
    {
        $cacheItem = $this->cache->getItem(
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_COUNT_IDENTIFIER, [$ownerId], true)
        );

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countNotifications($ownerId);

        $cacheItem->set($count);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationById(int $notificationId): Notification
    {
        $cacheItem = $this->cache->getItem(
            $this->cacheIdentifierGenerator->generateKey(self::NOTIFICATION_IDENTIFIER, [$notificationId], true)
        );

        $notification = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $notification;
        }

        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notificationId,
        ]);

        $notification = $this->persistenceHandler->notificationHandler()->getNotificationById($notificationId);

        $cacheItem->set($notification);
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
