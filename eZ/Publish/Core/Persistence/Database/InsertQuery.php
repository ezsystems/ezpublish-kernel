<?php

/**
 * File containing an interface for the database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Database;

/**
 * @property-read \eZ\Publish\Core\Persistence\Database\Expression $expr
 */
interface InsertQuery extends Query
{
    /**
     * Opens the query and sets the target table to $table.
     *
     * insertInto() returns a pointer to $this.
     *
     * @param string $table
     *
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function insertInto($table);

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
    public function set($column, $expression);
}
