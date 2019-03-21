<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Notification\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Legacy\Notification\Gateway;
use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use PDO;

class DoctrineDatabase extends Gateway
{
    const TABLE_NOTIFICATION = 'eznotification';
    const COLUMN_ID = 'id';
    const COLUMN_OWNER_ID = 'owner_id';
    const COLUMN_IS_PENDING = 'is_pending';
    const COLUMN_TYPE = 'type';
    const COLUMN_CREATED = 'created';
    const COLUMN_DATA = 'data';

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(CreateStruct $createStruct): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::TABLE_NOTIFICATION)
            ->values([
                self::COLUMN_IS_PENDING => ':is_pending',
                self::COLUMN_OWNER_ID => ':user_id',
                self::COLUMN_CREATED => ':created',
                self::COLUMN_TYPE => ':type',
                self::COLUMN_DATA => ':data',
            ])
            ->setParameter(':is_pending', $createStruct->isPending, PDO::PARAM_BOOL)
            ->setParameter(':user_id', $createStruct->ownerId, PDO::PARAM_INT)
            ->setParameter(':created', $createStruct->created, PDO::PARAM_INT)
            ->setParameter(':type', $createStruct->type, PDO::PARAM_STR)
            ->setParameter(':data', json_encode($createStruct->data), PDO::PARAM_STR);

        $query->execute();

        return (int) $this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationById(int $notificationId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(...$this->getColumns())
            ->from(self::TABLE_NOTIFICATION)
            ->where($query->expr()->eq(self::COLUMN_ID, ':id'));

        $query->setParameter(':id', $notificationId, PDO::PARAM_INT);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function updateNotification(Notification $notification): void
    {
        if (!isset($notification->id) || !is_numeric($notification->id)) {
            throw new InvalidArgumentException(self::COLUMN_ID, 'Cannot update Notification');
        }

        $query = $this->connection->createQueryBuilder();

        $query
            ->update(self::TABLE_NOTIFICATION)
            ->set(self::COLUMN_IS_PENDING, ':is_pending')
            ->where($query->expr()->eq(self::COLUMN_ID, ':id'))
            ->setParameter(':is_pending', $notification->isPending, PDO::PARAM_BOOL)
            ->setParameter(':id', $notification->id, PDO::PARAM_INT);

        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function countUserNotifications(int $userId): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('COUNT(' . self::COLUMN_ID . ')')
            ->from(self::TABLE_NOTIFICATION)
            ->where($query->expr()->eq(self::COLUMN_OWNER_ID, ':user_id'))
            ->setParameter(':user_id', $userId, PDO::PARAM_INT);

        return (int)$query->execute()->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function countUserPendingNotifications(int $userId): int
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select('COUNT(' . self::COLUMN_ID . ')')
            ->from(self::TABLE_NOTIFICATION)
            ->where($expr->eq(self::COLUMN_OWNER_ID, ':user_id'))
            ->andWhere($expr->eq(self::COLUMN_IS_PENDING, ':is_pending'))
            ->setParameter(':is_pending', true, PDO::PARAM_BOOL)
            ->setParameter(':user_id', $userId, PDO::PARAM_INT);

        return (int)$query->execute()->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserNotifications(int $userId, int $offset = 0, int $limit = -1): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(...$this->getColumns())
            ->from(self::TABLE_NOTIFICATION)
            ->where($query->expr()->eq(self::COLUMN_OWNER_ID, ':user_id'))
            ->setFirstResult($offset);

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        $query->orderBy(self::COLUMN_ID, 'DESC');
        $query->setParameter(':user_id', $userId, PDO::PARAM_INT);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $notificationId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::TABLE_NOTIFICATION)
            ->where($query->expr()->eq(self::COLUMN_ID, ':id'))
            ->setParameter(':id', $notificationId, PDO::PARAM_INT);

        $query->execute();
    }

    /**
     * @return array
     */
    private function getColumns(): array
    {
        return [
            self::COLUMN_ID,
            self::COLUMN_OWNER_ID,
            self::COLUMN_IS_PENDING,
            self::COLUMN_TYPE,
            self::COLUMN_CREATED,
            self::COLUMN_DATA,
        ];
    }
}
