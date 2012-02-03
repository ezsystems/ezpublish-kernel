<?php
/**
 * File containing a wrapper for the DB handler
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\Core\Persistence\Legacy;
use ezcDbHandler as ezcDbHandlerWrapped,
    ezcQuerySelect;

/**
 * Wrapper class for the zeta components database handler, providing some
 * additional utility functions.
 *
 * Functions as a full proxy to the zeta components database class.
 */
class EzcDbHandler
{
    /**
     * Aggregated zeta compoenents database handler, which is target of the
     * method dispatching.
     *
     * @var \ezcDbHandler
     */
    protected $ezcDbHandler;

    /**
     * Construct from zeta components database handler
     *
     * @param \ezcDbHandler $ezcDbHandler
     * @return void
     */
    public function __construct( ezcDbHandlerWrapped $ezcDbHandler )
    {
        $this->ezcDbHandler = $ezcDbHandler;
    }

    /**
     * Proxy methods to the aggregated DB handler
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call( $method, $parameters )
    {
        return call_user_func_array( array( $this->ezcDbHandler, $method ), $parameters );
    }

    /**
     * Creates an alias for $tableName, $columnName in $query.
     *
     * @param \ezcQuerySelect $query
     * @param string $columnName
     * @param string|null $tableName
     * @return string
     */
    public function aliasedColumn( ezcQuerySelect $query, $columnName, $tableName = null )
    {
        return $query->alias(
            $this->quoteColumn( $columnName, $tableName ),
            $this->ezcDbHandler->quoteIdentifier(
                ( $tableName ? $tableName . '_' : '' ) .
                $columnName
            )
        );
    }

    /**
     * Returns a qualified identifier for $columnName in $tableName.
     *
     * @param string $columnName
     * @param string $tableName
     * @return string
     */
    public function quoteColumn( $columnName, $tableName = null )
    {
        // @TODO: For oracle we need a mapping of table and column names to
        // their shortened variants here.
        return
            ( $tableName ? $this->quoteTable( $tableName ) . '.' : '' ) .
            $this->ezcDbHandler->quoteIdentifier( $columnName );
    }

    /**
     * Returns a qualified identifier for $tableName.
     *
     * @param string $tableName
     * @return string
     */
    public function quoteTable( $tableName )
    {
        // @TODO: For oracle we need a mapping of table and column names to
        // their shortened variants here.
        return $this->ezcDbHandler->quoteIdentifier( $tableName );
    }

    /**
     * Get auto increment value
     *
     * Returns the value used for autoincrement tables. Usually this will just
     * be null. In case for sequence based RDBMS this method can return a
     * proper value for the given column.
     *
     * @param string $table
     * @param string $column
     * @return mixed
     */
    public function getAutoIncrementValue( $table, $column )
    {
        return "null";
    }

    /**
     * Return the name of the affected sequence
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    public function getSequenceName( $table, $column )
    {
        return null;
    }
}

