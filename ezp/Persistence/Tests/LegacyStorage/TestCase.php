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
        return \ezcDbFactory::create( $this->getDsn() );
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
}
