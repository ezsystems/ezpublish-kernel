<?php

/**
 * File containing an interface for the Doctrine database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine;

use eZ\Publish\Core\Persistence\Database\QueryException;
use eZ\Publish\Core\Persistence\Database\DeleteQuery;

/**
 * Class DeleteDoctrineQuery.
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
class DeleteDoctrineQuery extends AbstractDoctrineQuery implements DeleteQuery
{
    /** @var string */
    private $table;

    /** @var array */
    private $where = [];

    /**
     * Opens the query and sets the target table to $table.
     *
     * deleteFrom() returns a pointer to $this.
     *
     * @param string $table
     *
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function deleteFrom($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Adds a where clause with logical expressions to the query.
     *
     * where() accepts an arbitrary number of parameters. Each parameter
     * must contain a logical expression or an array with logical expressions.
     * where() could be invoked several times. All provided arguments
     * added to the end of $whereString and form final WHERE clause of the query.
     * If you specify multiple logical expression they are connected using
     * a logical and.
     *
     * Example:
     * <code>
     * $q->deleteFrom( 'MyTable' )->where( $q->eq( 'id', 1 ) );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with no parameters.
     *
     * @param string|array(string) $... Either a string with a logical expression name
     * or an array with logical expressions.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function where()
    {
        $args = $this->parseArguments(func_get_args());

        foreach ($args as $whereExpression) {
            $this->where[] = $whereExpression;
        }

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

        $where = count($this->where)
            ? ' WHERE ' . implode(' AND ', $this->where)
            : '';

        return 'DELETE FROM ' . $this->table . $where;
    }
}
