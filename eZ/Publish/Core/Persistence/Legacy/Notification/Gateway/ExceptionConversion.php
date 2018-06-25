<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Notification\Gateway;

use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Persistence\Legacy\Notification\Gateway;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway
     */
    protected $innerGateway;

    /**
     * ExceptionConversion constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Notification\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function createNotification(Notification $notification): int
    {
        try {
            return $this->innerGateway->createNotification($notification);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationById(int $notificationId): array
    {
        try {
            return $this->innerGateway->getNotificationById($notificationId);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateNotification(Notification $notification): void
    {
        try {
            $this->innerGateway->updateNotification($notification);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countUserNotifications(int $userId): int
    {
        try {
            return $this->innerGateway->countUserNotifications($userId);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countUserPendingNotifications(int $userId): int
    {
        try {
            return $this->innerGateway->countUserPendingNotifications($userId);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserNotifications(int $userId, int $offset = 0, int $limit = -1): array
    {
        try {
            return $this->innerGateway->loadUserNotifications($userId, $offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
