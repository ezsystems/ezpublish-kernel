<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway;

/**
 * Password Blacklist gateway implementation using the Doctrine database.
 */
class DoctrineDatabase extends Gateway
{
    const TABLE_NAME = 'ezpasswordblacklist';

    const COLUMN_ID = 'id';
    const COLUMN_PASSWORD = 'password';

    /**
     * @var \Doctrine\DBAL\Connection
     */
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
    public function isBlacklisted(string $password): bool
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(' . self::COLUMN_ID . ')')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    self::COLUMN_PASSWORD,
                    $queryBuilder->createNamedParameter($password)
                )
            );

        return (int)$queryBuilder->execute()->fetchColumn() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(iterable $passwords): void
    {
        $query = $this->connection
            ->createQueryBuilder()
            ->insert(self::TABLE_NAME)
            ->values(['password' => ':password']);

        foreach ($passwords as $password) {
            if ($this->isBlacklisted($password)) {
                continue;
            }

            $query->setParameter('password', $password, ParameterType::STRING);
            $query->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(): void
    {
        $this->connection
            ->createQueryBuilder()
            ->delete(self::TABLE_NAME)
            ->execute();
    }
}
