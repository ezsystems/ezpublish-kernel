<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\Utils class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;

use eZ\Publish\Core\Repository\Tests\Service\InMemory\Utils as InMemoryUtils;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

/**
 * Utils class for InMemory tesst
 */
abstract class Utils extends InMemoryUtils
{
    /**
     * @static
     * @return \eZ\Publish\API\Repository\Repository
     */
    public static function getRepository()
    {
        // Override to set legacy handlers
        $sc = self::getServiceContainer(
            '@persistence_handler_legacy',
            '@io_handler_legacy',
            ( !empty( $_ENV['DATABASE'] ) ? $_ENV['DATABASE'] : 'sqlite://:memory:' )
        );

        // And inject data
        self::insertLegacyData( $sc->get( 'legacy_db_handler' ) );

        // Return repository
        return $sc->get( 'inner_repository' );
    }

    /**
     * @static
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $handler
     * @throws \Exception
     */
    protected static function insertLegacyData( EzcDbHandler $handler )
    {
        $dsn = ( !empty( $_ENV['DATABASE'] ) ? $_ENV['DATABASE'] : 'sqlite://:memory:' );
        $db = preg_replace( '(^([a-z]+).*)', '\\1', $dsn );
        $legacyHandlerDir = "eZ/Publish/Core/Persistence";

        // Insert Schema
        $schema = $legacyHandlerDir . '/Legacy/Tests/_fixtures/schema.' . $db . '.sql';
        $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $schema ) ) );
        foreach ( $queries as $query )
        {
            $handler->exec( $query );
        }


        // Insert some default data
        $data = require __DIR__ . '/_fixtures/clean_ezflow_dump.php';
        foreach ( $data as $table => $rows )
        {
            // Check that at least one row exists
            if ( !isset( $rows[0] ) )
            {
                continue;
            }

            $q = $handler->createInsertQuery();
            $q->insertInto( $handler->quoteIdentifier( $table ) );

            // Contains the bound parameters
            $values = array();

            // Binding the parameters
            foreach ( $rows[0] as $col => $val )
            {
                $q->set(
                    $handler->quoteIdentifier( $col ),
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

        if ( $db === 'pgsql' )
        {
            // Update PostgreSQL sequences
            $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $legacyHandlerDir . '/Legacy/Tests/_fixtures/setval.pgsql.sql' ) ) );
            foreach ( $queries as $query )
            {
                $handler->exec( $query );
            }
        }
    }
}
