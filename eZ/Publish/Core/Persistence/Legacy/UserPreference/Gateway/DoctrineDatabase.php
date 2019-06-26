<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct;

class DoctrineDatabase extends Gateway
{
    const TABLE_USER_PREFERENCES = 'ezpreferences';

    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'name';
    const COLUMN_USER_ID = 'user_id';
    const COLUMN_VALUE = 'value';

    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserPreference(UserPreferenceSetStruct $userPreference): int
    {
        $query = $this->connection->createQueryBuilder();

        $userPreferences = $this->getUserPreferenceByUserIdAndName($userPreference->userId, $userPreference->name);

        if (0 < count($userPreferences)) {
            $currentUserPreference = reset($userPreferences);
            $currentUserPreferenceId = (int)$currentUserPreference['id'];

            $query
                ->update(self::TABLE_USER_PREFERENCES)
                ->set(self::COLUMN_VALUE, ':value')
                ->where($query->expr()->eq(self::COLUMN_ID, ':id'))
                ->setParameter(':id', $currentUserPreferenceId, ParameterType::INTEGER)
                ->setParameter(':value', $userPreference->value, ParameterType::STRING);

            $query->execute();

            return $currentUserPreferenceId;
        }

        $query
            ->insert(self::TABLE_USER_PREFERENCES)
            ->values([
                self::COLUMN_NAME => ':name',
                self::COLUMN_USER_ID => ':user_id',
                self::COLUMN_VALUE => ':value',
            ])
            ->setParameter(':name', $userPreference->name, ParameterType::STRING)
            ->setParameter(':user_id', $userPreference->userId, ParameterType::INTEGER)
            ->setParameter(':value', $userPreference->value, ParameterType::STRING);

        $query->execute();

        return (int) $this->connection->lastInsertId();
    }

    public function getUserPreferenceByUserIdAndName(int $userId, string $name): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(...$this->getColumns())
            ->from(self::TABLE_USER_PREFERENCES)
            ->where($query->expr()->eq(self::COLUMN_USER_ID, ':userId'))
            ->andWhere($query->expr()->eq(self::COLUMN_NAME, ':name'));

        $query->setParameter(':userId', $userId, ParameterType::INTEGER);
        $query->setParameter(':name', $name, ParameterType::STRING);

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserPreferences(int $userId, int $offset = 0, int $limit = -1): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(...$this->getColumns())
            ->from(self::TABLE_USER_PREFERENCES)
            ->where($query->expr()->eq(self::COLUMN_USER_ID, ':user_id'))
            ->setFirstResult($offset);

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        $query->orderBy(self::COLUMN_ID, 'ASC');
        $query->setParameter(':user_id', $userId, ParameterType::INTEGER);

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function countUserPreferences(int $userId): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->getDatabasePlatform()->getCountExpression(self::COLUMN_ID)
            )
            ->from(self::TABLE_USER_PREFERENCES)
            ->where($query->expr()->eq(self::COLUMN_USER_ID, ':user_id'))
            ->setParameter(':user_id', $userId, ParameterType::INTEGER);

        return (int) $query->execute()->fetchColumn();
    }

    private function getColumns(): array
    {
        return [
            self::COLUMN_ID,
            self::COLUMN_NAME,
            self::COLUMN_USER_ID,
            self::COLUMN_VALUE,
        ];
    }
}
