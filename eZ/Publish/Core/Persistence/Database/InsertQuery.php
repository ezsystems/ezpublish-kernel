<?php

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
