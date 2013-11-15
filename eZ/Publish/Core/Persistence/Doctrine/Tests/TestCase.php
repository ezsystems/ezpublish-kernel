<?php

namespace eZ\Publish\Core\Persistence\Doctrine\Tests;

use PHPUnit_Framework_TestCase;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    protected $handler;

    public function setUp()
    {
        $dsn = getenv( "DATABASE" );

        if ( !$dsn )
        {
            $dsn = "sqlite://:memory:";
        }

        $doctrineParams = $this->convertDoctrineParams( $dsn );

        $this->connection = DriverManager::getConnection($doctrineParams);
        $this->handler = new ConnectionHandler($this->connection);
    }

    protected function createQueryTestTable()
    {
        $table = new \Doctrine\DBAL\Schema\Table('query_test');
        $table->addColumn( 'id', 'integer' );
        $table->addColumn( 'val1', 'string' );
        $table->addColumn( 'val2', 'integer' );

        try {
            $this->connection->getSchemaManager()->createTable( $table );
        } catch ( DBALException $e) {
        }
    }

    private function convertDoctrineParams( $dsn )
    {
        $params = $this->parseDSN( $dsn );

        $doctrineParams = array();

        switch ($params['phptype']) {
            case 'sqlite':
                $doctrineParams = array( 'driver' => 'pdo_sqlite' );

                if ($params['port'] === 'memory')
                {
                    $doctrineParams['memory'] = true;
                }
                else
                {
                    $doctrineParams['path'] = $params['database'];
                }

                break;
            case 'mysql':
                $doctrineParams = array(
                    'driver' => 'pdo_mysql',
                    'dbname' => $params['database'],
                    'username' => $params['username'],
                    'password' => $params['password'],
                    'port' => $params['port'] ?: 3306
                );

                break;
        }

        return $doctrineParams;
    }

    /**
     * Returns the Data Source Name as a structure containing the various parts of the DSN.
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DSN.
     *
     * The format of the supplied DSN is in its fullest form:
     * <code>
     *  phptype(dbsyntax)://username:password@protocol+hostspec/database?option=8&another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *  phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
     *  phptype://username:password@hostspec/database_name
     *  phptype://username:password@hostspec
     *  phptype://username@hostspec
     *  phptype://hostspec/database
     *  phptype://hostspec
     *  phptype(dbsyntax)
     *  phptype
     * </code>
     *
     * This function is 'borrowed' from PEAR /DB.php .
     *
     * @param string $dsn Data Source Name to be parsed
     *
     * @return array an associative array with the following keys:
     *  + phptype:  Database backend used in PHP (mysql, odbc etc.)
     *  + dbsyntax: Database used with regards to SQL syntax etc.
     *  + protocol: Communication protocol to use (tcp, unix etc.)
     *  + hostspec: Host specification (hostname[:port])
     *  + database: Database to use on the DBMS server
     *  + username: User name for login
     *  + password: Password for login
     */
    protected function parseDSN( $dsn )
    {
        $parsed = array(
            'phptype'  => false,
            'dbsyntax' => false,
            'username' => false,
            'password' => false,
            'protocol' => false,
            'hostspec' => false,
            'port'     => false,
            'socket'   => false,
            'database' => false,
        );

        if ( is_array( $dsn ) )
        {
            $dsn = array_merge( $parsed, $dsn );
            if ( !$dsn['dbsyntax'] )
            {
                $dsn['dbsyntax'] = $dsn['phptype'];
            }
            return $dsn;
        }

        // Find phptype and dbsyntax
        if ( ( $pos = strpos( $dsn, '://' ) ) !== false )
        {
            $str = substr( $dsn, 0, $pos );
            $dsn = substr( $dsn, $pos + 3 );
        }
        else
        {
            $str = $dsn;
            $dsn = null;
        }

        // Get phptype and dbsyntax
        // $str => phptype(dbsyntax)
        if ( preg_match( '|^(.+?)\((.*?)\)$|', $str, $arr ) )
        {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        }
        else
        {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }

        if ( !count( $dsn ) )
        {
            return $parsed;
        }

        // Get (if found): username and password
        // $dsn => username:password@protocol+hostspec/database
        if ( ( $at = strrpos( (string) $dsn, '@' ) ) !== false )
        {
            $str = substr( $dsn, 0, $at );
            $dsn = substr( $dsn, $at + 1 );
            if ( ( $pos = strpos( $str, ':' ) ) !== false )
            {
                $parsed['username'] = rawurldecode( substr( $str, 0, $pos ) );
                $parsed['password'] = rawurldecode( substr( $str, $pos + 1 ) );
            }
            else
            {
                $parsed['username'] = rawurldecode( $str );
            }
        }

        // Find protocol and hostspec

        if ( preg_match( '|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match ) )
        {
            // $dsn => proto(proto_opts)/database
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : false;
            $dsn         = $match[3];
        }
        else
        {
            // $dsn => protocol+hostspec/database (old format)
            if ( strpos( $dsn, '+' ) !== false )
            {
                list( $proto, $dsn ) = explode( '+', $dsn, 2 );
            }
            if ( strpos( $dsn, '/' ) !== false )
            {
                list( $proto_opts, $dsn ) = explode( '/', $dsn, 2 );
            }
            else
            {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = ( !empty( $proto ) ) ? $proto : 'tcp';
        $proto_opts = rawurldecode( $proto_opts );
        if ( $parsed['protocol'] == 'tcp' )
        {
            if ( strpos( $proto_opts, ':' ) !== false )
            {
                list( $parsed['hostspec'], $parsed['port'] ) = explode( ':', $proto_opts );
            }
            else
            {
                $parsed['hostspec'] = $proto_opts;
            }
        }
        elseif ( $parsed['protocol'] == 'unix' )
        {
            $parsed['socket'] = $proto_opts;
        }

        // Get dabase if any
        // $dsn => database
        if ( $dsn )
        {
            if ( ( $pos = strpos( $dsn, '?' ) ) === false )
            {
                // /database
                $parsed['database'] = rawurldecode( $dsn );
            }
            else
            {
                // /database?param1=value1&param2=value2
                $parsed['database'] = rawurldecode( substr( $dsn, 0, $pos ) );
                $dsn = substr( $dsn, $pos + 1 );
                if ( strpos( $dsn, '&') !== false )
                {
                    $opts = explode( '&', $dsn );
                }
                else
                { // database?param1=value1
                    $opts = array( $dsn );
                }
                foreach ( $opts as $opt )
                {
                    list( $key, $value ) = explode( '=', $opt );
                    if ( !isset( $parsed[$key] ) )
                    {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode( $value );
                    }
                }
            }
        }
        return $parsed;
    }
}
