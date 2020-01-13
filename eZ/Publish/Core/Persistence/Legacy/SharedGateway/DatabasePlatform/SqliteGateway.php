<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\SharedGateway\DatabasePlatform;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\SharedGateway\Gateway;

final class SqliteGateway implements Gateway
{
    /**
     * Error code 7 for a fatal error - taken from an existing driver implementation.
     */
    private const FATAL_ERROR_CODE = 7;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $databasePlatform;

    /** @var int[] */
    private $lastInsertedIds = [];

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->databasePlatform = $connection->getDatabasePlatform();
    }

    public function getColumnNextIntegerValue(
        string $tableName,
        string $columnName,
        string $sequenceName
    ): ?int {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->databasePlatform->getMaxExpression($columnName))
            ->from($tableName);

        $lastId = (int)$query->execute()->fetch(FetchMode::COLUMN);

        $this->lastInsertedIds[$sequenceName] = $lastId + 1;

        return $this->lastInsertedIds[$sequenceName];
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\DatabaseException if the sequence has no last value
     */
    public function getLastInsertedId(string $sequenceName): int
    {
        if (!isset($this->lastInsertedIds[$sequenceName])) {
            throw new DatabaseException(
                "Sequence '{$sequenceName}' is not yet defined in this session",
                self::FATAL_ERROR_CODE
            );
        }

        return $this->lastInsertedIds[$sequenceName];
    }
}
