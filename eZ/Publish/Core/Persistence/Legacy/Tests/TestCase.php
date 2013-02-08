<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\TestCase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use ezcQuerySelect;

/**
 * Base test case for database related tests
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * DSN used for the DB backend
     *
     * @var string
     */
    protected $dsn;

    /**
     * Name of the DB, extracted from DSN
     *
     * @var string
     */
    protected $db;

    /**
     * Database handler -- to not be constructed twice for one test
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $handler;

    /**
     * Property which holds the state if this is the initial test run, so that
     * we should set up the database, or if this is any of the following test
     * runs, where it is sufficient to reset the database.
     */
    protected static $initial = true;

    /**
     * Get data source name
     *
     * The database connection string is read from an optional environment
     * variable "DATABASE" and defaults to an in-memory SQLite database.
     *
     * @return string
     */
    protected function getDsn()
    {
        if ( !$this->dsn )
        {
            $this->dsn = getenv( "DATABASE" );
            if ( !$this->dsn )
                $this->dsn = "sqlite://:memory:";
            $this->db = preg_replace( '(^([a-z]+).*)', '\\1', $this->dsn );
        }

        return $this->dsn;
    }

    /**
     * Get a ezcDbHandler
     *
     * Get a ezcDbHandler, which can be used to interact with the configured
     * database. The database connection string is read from an optional
     * environment variable "DATABASE" and defaults to an in-memory SQLite
     * database.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    public function getDatabaseHandler()
    {
        if ( !$this->handler )
        {
            $this->handler = EzcDbHandler::create( $this->getDsn() );
        }

        return $this->handler;
    }

    /**
     * Resets the database on test setup, so we always operate on a clean
     * database.
     *
     * @return void
     */
    public function setUp()
    {
        if ( !class_exists( 'ezcBase' ) )
        {
            $this->markTestSkipped( 'Missing Apache Zeta Components.' );
        }

        try
        {
            $handler = $this->getDatabaseHandler();
        }
        catch ( \PDOException $e )
        {
            $this->markTestSkipped(
                'PDO session could not be created: ' . $e->getMessage()
            );
        }

        $schema = __DIR__ . '/_fixtures/schema.' . $this->db . '.sql';

        $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $schema ) ) );
        foreach ( $queries as $query )
        {
            $handler->exec( $query );
        }

        $this->resetSequences();

        // Set "global" static var, that we are behind the initial run
        self::$initial = false;
    }

    protected function tearDown()
    {
        unset( $this->handler );
    }

    /**
     * Get a text representation of a result set
     *
     * @param array $result
     *
     * @return string
     */
    protected static function getResultTextRepresentation( array $result )
    {
        return implode(
            "\n",
            array_map(
                function ( $row )
                {
                    return implode( ', ', $row );
                },
                $result
            )
        );
    }

    /**
     * Inserts database fixture from $file.
     *
     * @param string $file
     *
     * @return void
     */
    protected function insertDatabaseFixture( $file )
    {
        $data = require $file;
        $db = $this->getDatabaseHandler();

        foreach ( $data as $table => $rows )
        {
            // Check that at least one row exists
            if ( !isset( $rows[0] ) )
            {
                continue;
            }

            $q = $db->createInsertQuery();
            $q->insertInto( $db->quoteIdentifier( $table ) );

            // Contains the bound parameters
            $values = array();

            // Binding the parameters
            foreach ( $rows[0] as $col => $val )
            {
                $q->set(
                    $db->quoteIdentifier( $col ),
                    $q->bindParam( $values[$col] )
                );
            }

            $stmt = $q->prepare();

            foreach ( $rows as $row )
            {
                try
                {
                    // This CANNOT be replaced by:
                    // $values = $row
                    // each $values[$col] is a PHP reference which should be
                    // kept for parameters binding to work
                    foreach ( $row as $col => $val )
                    {
                        $values[$col] = $val;
                    }

                    $stmt->execute();
                }
                catch ( \Exception $e )
                {
                    echo "$table ( ", implode( ', ', $row ), " )\n";
                    throw $e;
                }
            }
        }

        $this->resetSequences();
    }

    /**
     * Reset DB sequences
     *
     * @return void
     */
    public function resetSequences()
    {
        switch ( $this->db )
        {
            case 'pgsql':
                // Update PostgreSQL sequences
                $handler = $this->getDatabaseHandler();

                $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( __DIR__ . '/_fixtures/setval.pgsql.sql' ) ) );
                foreach ( $queries as $query )
                {
                    $handler->exec( $query );
                }
        }
    }

    /**
     * Assert query result as correct
     *
     * Builds text representations of the asserted and fetched query result,
     * based on a ezcQuerySelect object. Compares them using classic diff for
     * maximum readability of the differences between expectations and real
     * results.
     *
     * The expectation MUST be passed as a two dimensional array containing
     * rows of columns.
     *
     * @param array $expectation
     * @param \ezcQuerySelect $query
     * @param string $message
     *
     * @return void
     */
    public static function assertQueryResult( array $expectation, ezcQuerySelect $query, $message = null )
    {
        $statement = $query->prepare();
        $statement->execute();

        $result = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $result[] = $row;
        }

        return self::assertEquals(
            self::getResultTextRepresentation( $expectation ),
            self::getResultTextRepresentation( $result ),
            $message
        );
    }

    /**
     * Asserts correct property values on $object.
     *
     * Asserts that for all keys in $properties a corresponding property
     * exists in $object with the *same* value as in $properties.
     *
     * @param array $properties
     * @param object $object
     *
     * @return void
     */
    protected function assertPropertiesCorrect( array $properties, $object )
    {
        if ( !is_object( $object ) )
        {
            throw new \InvalidArgumentException(
                'Expected object as second parameter, received ' . gettype( $object )
            );
        }
        foreach ( $properties as $propName => $propVal )
        {
            $this->assertSame(
                $propVal,
                $object->$propName,
                "Incorrect value for \${$propName}"
            );
        }
    }

    /**
     * Asserts $expStruct equals $actStruct in at least $propertyNames
     *
     * Asserts that properties of $actStruct equal properties of $expStruct (not
     * vice versa!). If $propertyNames is null, all properties are checked.
     * Otherwise, $propertyNames provides a white list.
     *
     * @param object $expStruct
     * @param object $actStruct
     * @param array $propertyNames
     */
    protected function assertStructsEqual(
        $expStruct, $actStruct, array $propertyNames = null )
    {
        if ( $propertyNames === null )
        {
            $propertyNames = $this->getPublicPropertyNames( $expStruct );
        }
        foreach ( $propertyNames as $propName )
        {
            $this->assertEquals(
                $expStruct->$propName,
                $actStruct->$propName,
                "Properties \${$propName} not same"
            );
        }
    }

    /**
     * Returns public property names in $object
     *
     * @param object $object
     *
     * @return array
     */
    protected function getPublicPropertyNames( $object )
    {
        $refl = new ReflectionObject( $object );
        return array_map(
            function ( $prop )
            {
                return $prop->getName();
            },
            $refl->getProperties( ReflectionProperty::IS_PUBLIC )
        );
    }

    /**
     * @return string
     */
    static protected function getInstallationDir()
    {
        static $installDir = null;
        if ( $installDir === null )
        {
            $config = require 'config.php';
            $installDir = $config['service']['parameters']['install_dir'];
        }
        return $installDir;
    }
}
