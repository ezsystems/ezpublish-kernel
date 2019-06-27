<?php

/**
 * File containing an interface for the Doctrine database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine;

use eZ\Publish\Core\Persistence\Database\InsertQuery;
use eZ\Publish\Core\Persistence\Database\QueryException;

/**
 * Class InsertDoctrineQuery.
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
class InsertDoctrineQuery extends AbstractDoctrineQuery implements InsertQuery
{
    /** @var string */
    private $table;

    /** @var array */
    private $values = [];

    /**
     * Opens the query and sets the target table to $table.
     *
     * insertInto() returns a pointer to $this.
     *
     * @param string $table
     *
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function insertInto($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * The insert query will set the column $column to the value $expression.
     *
     * set() returns a pointer to $this.
     *
     * @param string $column
     * @param string $expression
     *
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function set($column, $expression)
    {
        $this->values[$column] = $expression;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        if (strlen($this->table) === 0) {
            throw new QueryException('Missing table name');
        }

        if (count($this->values) === 0) {
            throw new QueryException('Missing values');
        }

        return 'INSERT INTO ' . $this->table
               . ' (' . implode(', ', array_keys($this->values)) . ')'
               . ' VALUES (' . implode(', ', $this->values) . ')';
    }
}
