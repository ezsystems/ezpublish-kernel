<?php

namespace eZ\Publish\Core\Persistence\Doctrine;

use eZ\Publish\Core\Persistence\Database\UpdateQuery;
use eZ\Publish\Core\Persistence\Database\QueryException;

class UpdateDoctrineQuery extends AbstractDoctrineQuery implements UpdateQuery
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $values = array();

    /**
     * @var array
     */
    private $where = array();

    /**
     * Opens the query and sets the target table to $table.
     *
     * update() returns a pointer to $this.
     *
     * @param string $table
     * @return \eZ\Publish\Core\Persistence\Database\UpdateQuery
     */
    public function update( $table )
    {
        $this->table = $table;

        return $this;
    }

    /**
     * The update query will set the column $column to the value $expression.
     *
     * @param string $column
     * @param string $expression
     * @return \eZ\Publish\Core\Persistence\Database\UpdateQuery
     */
    public function set( $column, $expression )
    {
        $this->values[$column] = $expression;

        return $this;
    }

    /**
     * Adds a where clause with logical expressions to the query.
     *
     * where() accepts an arbitrary number of parameters. Each parameter
     * must contain a logical expression or an array with logical expressions.
     * If you specify multiple logical expression they are connected using
     * a logical and.
     * where() could be invoked several times. All provided arguments
     * added to the end of $whereString and form final WHERE clause of the query.
     *
     *
     * Example:
     * <code>
     * $q->update( 'MyTable' )->where( $q->expr->eq( 'id', 1 ) );
     * </code>
     *
     * @throws \eZ\Publish\Core\Persistence\Database\QueryException if called with no parameters.
     * @param string|array(string) $... Either a string with a logical expression name
     * or an array with logical expressions.
     * @return \eZ\Publish\Core\Persistence\Database\UpdateQuery
     */
    public function where()
    {
        $args = $this->parseArguments( func_get_args() );

        foreach ( $args as $whereExpression )
        {
            $this->where[] = $whereExpression;
        }
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        if ( strlen( $this->table ) === 0)
        {
            throw new QueryException( 'Missing table name' );
        }

        if ( count( $this->values ) === 0 )
        {
            throw new QueryException( 'Missing values' );
        }

        if ( count( $this->where ) === 0 )
        {
            throw new QueryException( 'Executing update with where clause not allowed' );
        }

        $set = array();

        foreach ($this->values as $column => $expression )
        {
            $set[] = $column . ' = ' . $expression;
        }

        return 'UPDATE ' . $this->table . ' SET ' . implode( ', ', $set )
             . ' WHERE ' . implode( ' AND ', $this->where );
    }
}
