<?php
/**
 * File containing a wrapper for the DB handler
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy;

/**
 * Wrapper class for the zeta components database handler, providing some
 * additional utility functions.
 *
 * Functions as a full proxy to the zeta components database class.
 *
 * @version //autogentag//
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
    public function __construct( \ezcDbHandler $ezcDbHandler )
    {
        $this->ezcDbHandler = $ezcDbHandler;
    }

    /**
     * Proxy methods to the aggregated DB handler
     *
     * @param string $method
     * @param array $parameters
     * @return void
     */
    public function __call( $method, $parameters )
    {
        return call_user_func_array( array( $this->ezcDbHandler, $method ), $parameters );
    }

    /**
     * Creates an alias for $tableName, $columnName in $query.
     *
     * @param ezcDbQuery $query
     * @param string $columnName
     * @param string $tableName
     * @return string
     */
    public function aliasedColumn( \ezcQuerySelect $query, $columnName, $tableName = null )
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
            ( $tableName ? $this->ezcDbHandler->quoteIdentifier( $tableName ) . '.' : '' ) .
            $this->ezcDbHandler->quoteIdentifier( $columnName );
    }
}

