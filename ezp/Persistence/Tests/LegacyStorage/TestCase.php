<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\TestCase class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage;

/**
 * Base test case for database related tests
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * DSN used for the DB backend
     *
     * @var string
     */
    protected $dsn;

    /**
     * Database handler -- to not be constructed twice for one test
     *
     * @var \ezcDbHandler
     */
    protected $handler;

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
            $this->dsn = getenv( 'DATABASE' ) ?: 'sqlite://:memory:';
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
     * @return \ezcDbHandler
     */
    public function getDatabaseHandler()
    {
        if ( !$this->handler )
        {
            $this->handler = \ezcDbFactory::create( $this->getDsn() );
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
        $database = preg_replace( '(^([a-z]+).*)', '\\1', $this->getDsn() );
        $schema   = __DIR__ . '/_fixtures/schema.' . $database . '.sql';

        $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $schema ) ) );
        $handler = $this->getDatabaseHandler();
        foreach ( $queries as $query )
        {
            $handler->exec( $query );
        }
    }

    /**
     * Get a text representation of a result set
     *
     * @param array $result
     * @return string
     */
    protected static function getResultTextRepresentation( array $result )
    {
        return implode( "\n",
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
     * Assert query result as correct
     *
     * Vuilds text representations of the asserted and fetched query result,
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
     * @return void
     */
    public static function assertQueryResult( array $expectation, \ezcQuerySelect $query, $message = null )
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
}
