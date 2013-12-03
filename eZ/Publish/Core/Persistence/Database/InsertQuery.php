<?php
/**
 * File containing an interface for the database abstractions
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Database;

interface InsertQuery extends Query
{
    /**
     * Opens the query and sets the target table to $table.
     *
     * insertInto() returns a pointer to $this.
     *
     * @param string $table
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function insertInto( $table );

    /**
     * The insert query will set the column $column to the value $expression.
     *
     * set() returns a pointer to $this.
     *
     * @param string $column
     * @param string $expression
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function set( $column, $expression );
}
