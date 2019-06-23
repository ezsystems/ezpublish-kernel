<?php

/**
 * File containing an interface for the Doctrine database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;

use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;

class SqliteConnectionHandler extends ConnectionHandler
{
    protected $lastInsertedIds = [];

    /**
     * Retrieve the last auto incremet or sequence id.
     *
     * @param string $sequenceName
     *
     * @return string
     */
    public function lastInsertId($sequenceName = null)
    {
        if (isset($this->lastInsertedIds[$sequenceName])) {
            $lastInsertId = $this->lastInsertedIds[$sequenceName];
            unset($this->lastInsertedIds[$sequenceName]);

            return $lastInsertId;
        }

        return $this->connection->lastInsertId($sequenceName);
    }

    /**
     * Get auto increment value.
     *
     * Returns the value used for autoincrement tables. Usually this will just
     * be null. In case for sequence based RDBMS this method can return a
     * proper value for the given column.
     *
     * @param string $table
     * @param string $column
     *
     * @return mixed
     */
    public function getAutoIncrementValue($table, $column)
    {
        if (($table === 'ezcontentobject_attribute') && ($column === 'id')) {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->createSelectQuery();
            $q->select($q->expr->max('id'))->from('ezcontentobject_attribute');
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds['ezcontentobject_attribute.id'] = (int)$statement->fetchColumn() + 1;

            return $this->lastInsertedIds['ezcontentobject_attribute.id'];
        }

        if (($table === 'ezcontentclass') && ($column === 'id')) {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->createSelectQuery();
            $q->select($q->expr->max('id'))->from('ezcontentclass');
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds['ezcontentclass.id'] = (int)$statement->fetchColumn() + 1;

            return $this->lastInsertedIds['ezcontentclass.id'];
        }

        if (($table === 'ezcontentclass_attribute') && ($column === 'id')) {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->createSelectQuery();
            $q->select($q->expr->max('id'))->from('ezcontentclass_attribute');
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds['ezcontentclass_attribute.id'] = (int)$statement->fetchColumn() + 1;

            return $this->lastInsertedIds['ezcontentclass_attribute.id'];
        }

        return 'NULL';
    }

    /**
     * Returns the name of the affected sequence.
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getSequenceName($table, $column)
    {
        if (($table === 'ezcontentobject_attribute') && ($column === 'id')) {
            return "{$table}.{$column}";
        }

        if (($table === 'ezcontentclass') && ($column === 'id')) {
            return "{$table}.{$column}";
        }

        if (($table === 'ezcontentclass_attribute') && ($column === 'id')) {
            return "{$table}.{$column}";
        }

        return null;
    }
}
