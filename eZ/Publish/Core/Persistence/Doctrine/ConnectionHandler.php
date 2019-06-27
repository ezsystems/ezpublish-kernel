<?php

/**
 * File containing an interface for the Doctrine database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Doctrine;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\QueryException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException;

/**
 * Class ConnectionHandler.
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
class ConnectionHandler implements DatabaseHandler
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /**
     * @param string|array $dsn
     *
     * @return \Doctrine\DBAL\Connection
     */
    public static function createConnectionFromDSN($dsn)
    {
        if (is_string($dsn)) {
            $parsed = self::parseDSN($dsn);
        } else {
            $parsed = $dsn;
        }

        /**
         * @todo retry connection here.
         */
        return DriverManager::getConnection($parsed);
    }

    /**
     * Create a Connection Handler from given Doctrine $connection.
     *
     * @param \Doctrine\DBAL\Connection $connection
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    public static function createFromConnection(Connection $connection)
    {
        $driver = $connection->getDriver()->getName();

        if ($driver === 'pdo_sqlite') {
            return new ConnectionHandler\SqliteConnectionHandler($connection);
        }

        if ($driver === 'pdo_pgsql') {
            return new ConnectionHandler\PostgresConnectionHandler($connection);
        }

        return new self($connection);
    }

    /**
     * Create a Connection Handler with corresponding Doctrine connection from DSN.
     *
     * @param string|array $dsn
     *
     * @return ConnectionHandler
     */
    public static function createFromDSN($dsn)
    {
        if (is_string($dsn)) {
            $parsed = self::parseDSN($dsn);
        } else {
            $parsed = $dsn;
        }

        $connection = DriverManager::getConnection($parsed);

        if ($parsed['driver'] === 'pdo_sqlite') {
            return new ConnectionHandler\SqliteConnectionHandler($connection);
        }

        if ($parsed['driver'] === 'pdo_pgsql') {
            return new ConnectionHandler\PostgresConnectionHandler($connection);
        }

        return new self($connection);
    }

    /**
     * Returns the Data Source Name as a structure containing the various parts of the DSN.
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DSN.
     *
     * The format of the supplied DSN is in its fullest form:
     * <code>
     *  driver://user:password@protocol+host/database?option=8&another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *  driver://user:password@protocol+host:110//usr/db_file.db?mode=0644
     *  driver://user:password@host/dbname
     *  driver://user:password@host
     *  driver://user@host
     *  driver://host/dbname
     *  driver://host
     *  driver
     * </code>
     *
     * This function is 'borrowed' from PEAR /DB.php .
     *
     * @license Apache2 from Zeta Components Database project
     *
     * @param string $dsn Data Source Name to be parsed
     *
     * @return array an associative array with the following keys:
     *  + driver:  Database backend used in PHP (mysql, odbc etc.)
     *  + host: Host specification (hostname[:port])
     *  + dbname: Database to use on the DBMS server
     *  + username: User name for login
     *  + password: Password for login
     */
    public static function parseDSN($dsn)
    {
        $parsed = [
            'driver' => false,
            'user' => false,
            'password' => false,
            'host' => false,
            'port' => false,
            'unix_socket' => false,
            'dbname' => false,
            'memory' => false,
            'path' => false,
        ];

        // Find driver and dbsyntax
        if (($pos = strpos($dsn, '://')) !== false) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 3);
        } else {
            $str = $dsn;
            $dsn = null;
        }

        // Get driver and dbsyntax
        // $str => driver(dbsyntax)
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['driver'] = $arr[1];
        } else {
            $parsed['driver'] = $str;
        }

        if (empty($dsn)) {
            return $parsed;
        }

        // Get (if found): username and password
        // $dsn => username:password@protocol+host/database
        if (($at = strrpos((string)$dsn, '@')) !== false) {
            $str = substr($dsn, 0, $at);
            $dsn = substr($dsn, $at + 1);
            if (($pos = strpos($str, ':')) !== false) {
                $parsed['user'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['user'] = rawurldecode($str);
            }
        }

        // Find protocol and host

        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match)) {
            // $dsn => proto(proto_opts)/database
            $proto = $match[1];
            $proto_opts = $match[2] ?: false;
            $dsn = $match[3];
        } else {
            // $dsn => protocol+host/database (old format)
            if (strpos($dsn, '+') !== false) {
                list($proto, $dsn) = explode('+', $dsn, 2);
            }
            if (strpos($dsn, '/') !== false) {
                list($proto_opts, $dsn) = explode('/', $dsn, 2);
            } else {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }

        // process the different protocol options
        $protocol = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if ($protocol === 'tcp') {
            if (strpos($proto_opts, ':') !== false) {
                list($parsed['host'], $parsed['port']) = explode(':', $proto_opts);
            } else {
                $parsed['host'] = $proto_opts;
            }
        } elseif ($protocol === 'unix') {
            $parsed['unix_socket'] = $proto_opts;
        }

        // Get dabase if any
        // $dsn => database
        if ($dsn) {
            if (($pos = strpos($dsn, '?')) === false) {
                // /database
                $parsed['dbname'] = rawurldecode($dsn);
            } else {
                // /database?param1=value1&param2=value2
                $parsed['dbname'] = rawurldecode(substr($dsn, 0, $pos));
                $dsn = substr($dsn, $pos + 1);
                if (strpos($dsn, '&') !== false) {
                    $opts = explode('&', $dsn);
                } else {
                    $opts = [$dsn];
                }

                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }

        if ($parsed['driver'] === 'sqlite') {
            if (isset($parsed['port']) && $parsed['port'] === 'memory') {
                $parsed['memory'] = true;
                unset($parsed['port']);
                unset($parsed['host']);
            } elseif (isset($parsed['dbname'])) {
                $parsed['path'] = $parsed['dbname'];
                unset($parsed['dbname']);
                unset($parsed['host']);
            }
        }

        $driverMap = [
            'mysql' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
        ];

        if (isset($driverMap[$parsed['driver']])) {
            $parsed['driver'] = $driverMap[$parsed['driver']];
        }

        return array_filter(
            $parsed,
            function ($element) {
                return $element !== false;
            }
        );
    }

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->connection->getDatabasePlatform()->getName();
    }

    /**
     * Begin a transaction.
     */
    public function beginTransaction()
    {
        try {
            $this->connection->beginTransaction();
        } catch (DBALException $e) {
            throw new QueryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Commit a transaction.
     */
    public function commit()
    {
        try {
            $this->connection->commit();
        } catch (DBALException $e) {
            throw new QueryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Rollback a transaction.
     */
    public function rollBack()
    {
        try {
            $this->connection->rollBack();
        } catch (DBALException $e) {
            throw new QueryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function prepare($query)
    {
        return $this->connection->prepare($query);
    }

    /**
     * Retrieve the last auto incremet or sequence id.
     *
     * @param string $sequenceName
     *
     * @return string
     */
    public function lastInsertId($sequenceName = null)
    {
        return $this->connection->lastInsertId($sequenceName);
    }

    /**
     * @return bool
     */
    public function useSequences()
    {
        return $this->connection->getDatabasePlatform()->supportsSequences();
    }

    /**
     * Execute a query against the database.
     *
     * @param string $query
     */
    public function exec($query)
    {
        try {
            $this->connection->exec($query);
        } catch (DBALException $e) {
            throw new QueryException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Create Select Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function createSelectQuery()
    {
        return new SelectDoctrineQuery($this->connection);
    }

    /**
     * Create Insert Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function createInsertQuery()
    {
        return new InsertDoctrineQuery($this->connection);
    }

    /**
     * Create update Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\UpdateQuery
     */
    public function createUpdateQuery()
    {
        return new UpdateDoctrineQuery($this->connection);
    }

    /**
     * Create a Delete Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function createDeleteQuery()
    {
        return new DeleteDoctrineQuery($this->connection);
    }

    /**
     * Creates an alias for $tableName, $columnName in $query.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param string $columnName
     * @param string|null $tableName
     *
     * @return string
     */
    public function aliasedColumn($query, $columnName, $tableName = null)
    {
        return $this->alias(
            $this->quoteColumn($columnName, $tableName),
            $this->quoteIdentifier(
                ($tableName ? $tableName . '_' : '') .
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
    public function quoteColumn($columnName, $tableName = null)
    {
        return
            ($tableName ? $this->quoteTable($tableName) . '.' : '') .
            $this->quoteIdentifier($columnName);
    }

    /**
     * Returns a qualified identifier for $tableName.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function quoteTable($tableName)
    {
        return $this->quoteIdentifier($tableName);
    }

    /**
     * Custom alias method.
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function alias($name, $alias)
    {
        return $name . ' ' . $alias;
    }

    /**
     * Custom quote identifier method.
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return '`' . $identifier . '`';
    }

    /**
     * Get auto increment value.
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
    public function getAutoIncrementValue($table, $column)
    {
        return 'null';
    }

    /**
     * Returns the name of the affected sequence.
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getSequenceName($table, $column)
    {
        return null;
    }
}
