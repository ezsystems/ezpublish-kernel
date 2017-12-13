<?php

/**
 * File containing an interface for the database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Database;

/**
 * @property-read \eZ\Publish\Core\Persistence\Database\Expression $expr
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
interface DeleteQuery extends Query
{
    /**
     * Opens the query and sets the target table to $table.
     *
     * deleteFrom() returns a pointer to $this.
     *
     * @param string $table
     *
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function deleteFrom($table);

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
    public function where();
}
