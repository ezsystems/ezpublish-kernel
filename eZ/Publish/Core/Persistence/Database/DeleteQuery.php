<?php
/**
 * File containing an interface for the database abstractions
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Database;

interface DeleteQuery extends Query
{
    /**
     * Opens the query and sets the target table to $table.
     *
     * deleteFrom() returns a pointer to $this.
     *
     * @param string $table
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function deleteFrom( $table );

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
     * @param string|array(string) $... Either a string with a logical expression name
     * or an array with logical expressions.
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function where();
}
