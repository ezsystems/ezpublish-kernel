<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Notification\Gateway;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\Query;
use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use PDO;

class DoctrineDatabase implements Handler
{
    const TABLE_NOTIFICATION = 'eznotification';
    const COLUMN_ID = 'id';
    const COLUMN_OWNER_ID = 'owner_id';
    const COLUMN_IS_PENDING = 'is_pending';
    const COLUMN_TYPE = 'type';
    const COLUMN_CREATED = 'created';
    const COLUMN_DATA = 'data';

    /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler */
    protected $handler;

    /**
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     */
    public function __construct(DatabaseHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return mixed
     */
    public function createNotification(Notification $notification)
    {
        $handler = $this->handler;

        $handler->beginTransaction();

        /** @var \eZ\Publish\Core\Persistence\Database\InsertQuery $query */
        $query = $handler->createInsertQuery();
        $query->insertInto($handler->quoteTable(self::TABLE_NOTIFICATION));
        $this->bindColumnValue($query, self::COLUMN_OWNER_ID, $notification->ownerId);
        $this->bindColumnValue($query, self::COLUMN_TYPE, $notification->type);
        $this->bindColumnValue($query, self::COLUMN_CREATED, $notification->created);
        $this->bindColumnValue($query, self::COLUMN_DATA, json_encode($notification->data));
        $query->prepare()->execute();

        $notificationId = $handler->lastInsertId($handler->getSequenceName(self::TABLE_NOTIFICATION, self::COLUMN_ID));

        $handler->commit();

        return $notificationId;
    }

    /**
     * Get paginated users Notifications.
     *
     * @param mixed $ownerId
     * @param int $limit
     * @param int $page
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification[]
     */
    public function getNotificationsByOwnerId($ownerId, int $limit, int $page = 0): array
    {
        $handler = $this->handler;

        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable(self::TABLE_NOTIFICATION));
        $query->select(
            $handler->quoteColumn(self::COLUMN_ID, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_IS_PENDING, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_OWNER_ID, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_TYPE, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_CREATED, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_DATA, self::TABLE_NOTIFICATION)
        );
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn(self::COLUMN_OWNER_ID, self::TABLE_NOTIFICATION),
                $query->bindValue($ownerId, null, PDO::PARAM_INT)
            )
        );

        $query->orderBy(self::COLUMN_CREATED, 'DESC');
        $query->limit($limit, $page * $limit);

        $statement = $query->prepare();
        $statement->execute();

        $spiNotifications = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $notifications = [];

        foreach ($spiNotifications as $spi) {
            $notification = new Notification();

            $notification->id = $spi[self::COLUMN_ID];
            $notification->ownerId = $spi[self::COLUMN_OWNER_ID];
            $notification->isPending = $spi[self::COLUMN_IS_PENDING];
            $notification->created = $spi[self::COLUMN_CREATED];
            $notification->type = $spi[self::COLUMN_TYPE];
            $notification->data = json_decode($spi[self::COLUMN_DATA]);

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Count users unread Notifications.
     *
     * @param mixed $ownerId
     *
     * @return int
     */
    public function countPendingNotificationsByOwnerId($ownerId): int
    {
        $handler = $this->handler;

        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable(self::TABLE_NOTIFICATION));
        $query->select('COUNT(id)');
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn(self::COLUMN_OWNER_ID, self::TABLE_NOTIFICATION),
                $query->bindValue($ownerId, null, PDO::PARAM_INT)
            ),
            $query->expr->eq(
                $this->handler->quoteColumn(self::COLUMN_IS_PENDING, self::TABLE_NOTIFICATION),
                $query->bindValue(true, null, PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $count = (int)$statement->fetch(PDO::FETCH_COLUMN);

        return $count;
    }

    /**
     * Count total users Notifications.
     *
     * @param mixed $ownerId
     *
     * @return int
     */
    public function countNotificationsByOwnerId($ownerId): int
    {
        $handler = $this->handler;

        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable(self::TABLE_NOTIFICATION));
        $query->select('COUNT(id)');
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn(self::COLUMN_OWNER_ID, self::TABLE_NOTIFICATION),
                $query->bindValue($ownerId, null, PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $count = (int)$statement->fetch(PDO::FETCH_COLUMN);

        return $count;
    }

    /**
     * Get Notification by its id.
     *
     * @param mixed $notificationId
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     */
    public function getNotificationById($notificationId): Notification
    {
        $handler = $this->handler;

        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable(self::TABLE_NOTIFICATION));
        $query->select(
            $handler->quoteColumn(self::COLUMN_ID, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_IS_PENDING, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_OWNER_ID, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_TYPE, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_CREATED, self::TABLE_NOTIFICATION),
            $handler->quoteColumn(self::COLUMN_DATA, self::TABLE_NOTIFICATION)
        );
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn(self::COLUMN_ID, self::TABLE_NOTIFICATION),
                $query->bindValue($notificationId, null, PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $spiNotification = $statement->fetch(\PDO::FETCH_ASSOC);

        $notification = new Notification();

        if ($spiNotification) {
            $notification->id = $spiNotification[self::TABLE_NOTIFICATION];
            $notification->ownerId = $spiNotification[self::COLUMN_OWNER_ID];
            $notification->isPending = $spiNotification[self::COLUMN_IS_PENDING];
            $notification->created = $spiNotification[self::COLUMN_CREATED];
            $notification->type = $spiNotification[self::COLUMN_TYPE];
            $notification->data = json_decode($spiNotification[self::COLUMN_DATA]);
        }

        return $notification;
    }

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @todo
     *
     * @param \eZ\Publish\SPI\Persistence\Notification\Notification $notification
     *
     * @return \eZ\Publish\SPI\Persistence\Notification\Notification
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function updateNotification(Notification $notification): Notification
    {
        $handler = $this->handler;

        if (!isset($notification->id) || !is_numeric($notification->id)) {
            throw new InvalidArgumentException(self::COLUMN_ID, 'Cannot update Notification');
        }

        /** @var \eZ\Publish\Core\Persistence\Database\UpdateQuery $query */
        $query = $handler->createUpdateQuery();
        $query->update($handler->quoteTable(self::TABLE_NOTIFICATION));
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn(self::COLUMN_ID, self::TABLE_NOTIFICATION),
                $query->bindValue($notification->id, null, PDO::PARAM_INT)
            )
        );

        if (isset($notification->isPending)) {
            $this->bindColumnValue($query, self::COLUMN_IS_PENDING, (int)$notification->isPending);
        }

        $statement = $query->prepare();
        $statement->execute();

        return $notification;
    }

    /**
     * Helper to make insert value one-liners human readable.
     *
     * @internal
     *
     * @param \eZ\Publish\Core\Persistence\Database\Query $query Query to work on (context)
     * @param string $column Table column name
     * @param mixed $value Value to be insterted
     */
    protected function bindColumnValue(Query $query, string $column, $value)
    {
        $query->set(
            $this->handler->quoteColumn($column),
            $query->bindValue($value)
        );
    }
}
