<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Notification\Gateway;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\InsertQuery;
use eZ\Publish\Core\Persistence\Database\Query;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Database\UpdateQuery;
use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use PDO;

class DoctrineDatabase implements Handler
{
    /** @var DatabaseHandler $handler */
    protected $handler;

    /**
     * DoctrineDatabase constructor.
     *
     * @param DatabaseHandler $handler
     */
    public function __construct(DatabaseHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Store Notification ValueObject in persistent storage.
     *
     * @param Notification $notification
     *
     * @return int
     */
    public function createNotification(Notification $notification)
    {
        $handler = $this->handler;

        $handler->beginTransaction();

        /** @var InsertQuery $query */
        $query = $handler->createInsertQuery();
        $query->insertInto($handler->quoteTable('eznotification'));
        $this->bindColumnValue($query, 'owner_id', $notification->ownerId);
        $this->bindColumnValue($query, 'type', $notification->type);
        $this->bindColumnValue($query, 'created', $notification->created);
        $this->bindColumnValue($query, 'data', json_encode($notification->data));
        $query->prepare()->execute();

        $notificationId = $handler->lastInsertId($handler->getSequenceName('eznotification', 'id'));

        $handler->commit();

        return $notificationId;
    }

    /**
     * Get paginated users Notifications.
     *
     * @param int $ownerId
     * @param int $limit
     * @param int $page
     *
     * @return Notification[]
     */
    public function getNotificationsByOwnerId($ownerId, $limit, $page = 0)
    {
        $handler = $this->handler;

        /** @var SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable('eznotification'));
        $query->select(
            $handler->quoteColumn('id', 'eznotification'),
            $handler->quoteColumn('is_pending', 'eznotification'),
            $handler->quoteColumn('owner_id', 'eznotification'),
            $handler->quoteColumn('type', 'eznotification'),
            $handler->quoteColumn('created', 'eznotification'),
            $handler->quoteColumn('data', 'eznotification')
        );
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('owner_id', 'eznotification'),
                $query->bindValue($ownerId, null, PDO::PARAM_INT)
            )
        );

        $query->orderBy('created', 'DESC');
        $query->limit($limit, $page * $limit);

        $statement = $query->prepare();
        $statement->execute();

        $spiNotifications = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $notifications = [];

        foreach ($spiNotifications as $spi) {
            $notification = new Notification();

            $notification->id = $spi['id'];
            $notification->ownerId = $spi['owner_id'];
            $notification->isPending = $spi['is_pending'];
            $notification->created = $spi['created'];
            $notification->type = $spi['type'];
            $notification->data = json_decode($spi['data']);

            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Count users unread Notifications.
     *
     * @param int $ownerId
     *
     * @return int
     */
    public function countPendingNotificationsByOwnerId($ownerId)
    {
        $handler = $this->handler;

        /** @var SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable('eznotification'));
        $query->select('COUNT(id)');
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('owner_id', 'eznotification'),
                $query->bindValue($ownerId, null, PDO::PARAM_INT)
            ),
            $query->expr->eq(
                $this->handler->quoteColumn('is_pending', 'eznotification'),
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
     * @param int $ownerId
     *
     * @return int
     */
    public function countNotificationsByOwnerId($ownerId)
    {
        $handler = $this->handler;

        /** @var SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable('eznotification'));
        $query->select('COUNT(id)');
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('owner_id', 'eznotification'),
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
     * @param int $notificationId
     *
     * @return Notification
     */
    public function getNotificationById($notificationId)
    {
        $handler = $this->handler;

        /** @var SelectQuery $query */
        $query = $handler->createSelectQuery();
        $query->from($handler->quoteTable('eznotification'));
        $query->select(
            $handler->quoteColumn('id', 'eznotification'),
            $handler->quoteColumn('is_pending', 'eznotification'),
            $handler->quoteColumn('owner_id', 'eznotification'),
            $handler->quoteColumn('type', 'eznotification'),
            $handler->quoteColumn('created', 'eznotification'),
            $handler->quoteColumn('data', 'eznotification')
        );
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('id', 'eznotification'),
                $query->bindValue($notificationId, null, PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $spiNotification = $statement->fetch(\PDO::FETCH_ASSOC);

        $notification = new Notification();

        if ($spiNotification) {
            $notification->id = $spiNotification['id'];
            $notification->ownerId = $spiNotification['owner_id'];
            $notification->isPending = $spiNotification['is_pending'];
            $notification->created = $spiNotification['created'];
            $notification->type = $spiNotification['type'];
            $notification->data = json_decode($spiNotification['data']);
        }

        return $notification;
    }

    /**
     * Update Notification ValueObject in persistent storage.
     * There's no edit feature but it's essential to mark Notification as read.
     *
     * @todo
     *
     * @param Notification $notification
     *
     * @throws InvalidArgumentException
     *
     * @return Notification
     */
    public function updateNotification(Notification $notification)
    {
        $handler = $this->handler;

        if (!isset($notification->id) || !is_numeric($notification->id)) {
            throw new InvalidArgumentException('id', 'Cannot update Notification');
        }

        /** @var UpdateQuery $query */
        $query = $handler->createUpdateQuery();
        $query->update($handler->quoteTable('eznotification'));
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('id', 'eznotification'),
                $query->bindValue($notification->id, null, PDO::PARAM_INT)
            )
        );

        if (isset($notification->isPending)) {
            $this->bindColumnValue($query, 'is_pending', (int)$notification->isPending);
        }

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Helper to make insert value one-liners human readable.
     *
     * @internal
     *
     * @param Query $query Query to work on (context)
     * @param string $column Table column name
     * @param mixed $value Value to be insterted
     */
    protected function bindColumnValue(Query $query, $column, $value)
    {
        $query->set(
            $this->handler->quoteColumn($column),
            $query->bindValue($value)
        );
    }
}
