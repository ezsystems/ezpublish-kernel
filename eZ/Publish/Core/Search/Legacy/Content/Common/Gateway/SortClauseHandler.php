<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Handler for a single sort clause.
 */
abstract class SortClauseHandler
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform|null */
    protected $dbPlatform;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->dbPlatform = $connection->getDatabasePlatform();
    }

    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    abstract public function accept(SortClause $sortClause);

    /**
     * Apply selects to the query.
     *
     * Returns the name of the (aliased) column, which information should be
     * used for sorting.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return array column names to be used when sorting
     */
    abstract public function applySelect(QueryBuilder $query, SortClause $sortClause, int $number): array;

    /**
     * Applies joins to the query.
     *
     * @param array $languageSettings
     */
    public function applyJoin(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number,
        array $languageSettings
    ): void {
    }

    /**
     * Returns the quoted sort column name.
     *
     * @param int $number
     *
     * @return string
     */
    protected function getSortColumnName($number)
    {
        return $this->connection->quoteIdentifier('sort_column_' . $number);
    }

    /**
     * Returns the sort table name.
     *
     * @param int $number
     * @param null|string $externalTableName
     *
     * @return string
     */
    protected function getSortTableName($number, $externalTableName = null)
    {
        return 'sort_table_' . ($externalTableName !== null ? $externalTableName . '_' : '') . $number;
    }
}
