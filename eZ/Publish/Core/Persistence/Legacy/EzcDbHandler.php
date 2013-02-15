<?php
/**
 * File containing a wrapper for the DB handler
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy;

use ezcDbHandler as ezcDbHandlerWrapped;
use ezcQuerySelect;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler\Pgsql;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler\Sqlite;
use ezcDbFactory;

/**
 * Wrapper class for the zeta components database handler, providing some
 * additional utility functions.
 *
 * Functions as a full proxy to the zeta components database class.
 */
class EzcDbHandler
{
    /**
     * Aggregated zeta components database handler, which is target of the
     * method dispatching.
     *
     * @var \ezcDbHandler
     */
    protected $ezcDbHandler;

    /**
     * Construct from zeta components database handler
     *
     * @param \ezcDbHandler $ezcDbHandler
     */
    public function __construct( ezcDbHandlerWrapped $ezcDbHandler )
    {
        $this->ezcDbHandler = $ezcDbHandler;
    }

    /**
     * Factory for getting EzcDbHandler handler object
     *
     * Will use postgres or sqlite specific wrappers if dsn indicates such databases.
     *
     * The DSN (data source name) defines which database to use. It's format is
     * defined by the Apache Zeta Components Database component. Examples are:
     *
     * - mysql://root:secret@localhost/ezp
     *   For the MySQL database "ezp" on localhost, which will be accessed
     *   using user "root" with password "secret"
     * - sqlite://:memory:
     *   for a SQLite in memory database (used e.g. for unit tests)
     *
     * For further information on the database setup, please refer to
     * {@see http://incubator.apache.org/zetacomponents/documentation/trunk/Database/tutorial.html#handler-usage}
     *
     * @param string|mixed[]$dbParams
     *
     * @return EzcDbHandler
     */
    public static function create( $dbParams )
    {
        if ( !is_array( $dbParams ) )
        {
            $databaseType = preg_replace( '(^([a-z]+).*)', '\\1', $dbParams );
        }
        else
        {
            $databaseType = $dbParams['type'];
            // PDOMySQL ignores the "charset" param until PHP 5.3.6.
            // We then need to force it to use an init command.
            // @link http://php.net/manual/en/ref.pdo-mysql.connection.php
            if ( $databaseType === 'mysql' && $dbParams['charset'] === 'utf8' )
            {
                $dbParams['driver-opts'] += array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                );
            }
        }
        $connection = ezcDbFactory::create( $dbParams );

        switch ( $databaseType )
        {
            case 'pgsql':
                $dbHandler = new Pgsql( $connection );
                break;

            case 'sqlite':
                $dbHandler = new Sqlite( $connection );
                break;

            default:
                $dbHandler = new self( $connection );
        }
        return $dbHandler;
    }

    /**
     * Proxy methods to the aggregated DB handler
     *
     * @param string $method
     * @param array $parameters
     *
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
     *
     * @return string
     */
    public function aliasedColumn( ezcQuerySelect $query, $columnName, $tableName = null )
    {
        return $this->alias(
            $this->quoteColumn( $columnName, $tableName ),
            $this->quoteIdentifier(
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
     *
     * @return string
     */
    public function quoteColumn( $columnName, $tableName = null )
    {
        // @todo: For oracle we need a mapping of table and column names to
        // their shortened variants here.
        return
            ( $tableName ? $this->quoteTable( $tableName ) . '.' : '' ) .
            $this->quoteIdentifier( $columnName );
    }

    /**
     * Returns a qualified identifier for $tableName.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function quoteTable( $tableName )
    {
        // @todo: For oracle we need a mapping of table and column names to
        // their shortened variants here.
        return $this->quoteIdentifier( $tableName );
    }

    /**
     * Custom alias method
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     * @return string
     */
    public function alias( $name, $alias )
    {
        return $name . ' ' . $alias;
    }

    /**
     * Custom quote identifier method
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier( $identifier )
    {
        return '`' . $identifier . '`';
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
     *
     * @return mixed
     */
    public function getAutoIncrementValue( $table, $column )
    {
        return "null";
    }

    /**
     * Returns the name of the affected sequence
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getSequenceName( $table, $column )
    {
        return null;
    }
}

