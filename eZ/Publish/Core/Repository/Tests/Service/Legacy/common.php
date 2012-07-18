<?php
/**
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;
use eZ\Publish\Core\Base\ConfigurationManager,
    eZ\Publish\Core\Base\ServiceContainer,
    ReflectionMethod;

/**
 * Common init code for legacy service tests, returns repository
 */
$dsn = ( isset( $_ENV['DATABASE'] ) && $_ENV['DATABASE'] ) ? $_ENV['DATABASE'] : 'sqlite://:memory:';
$db = preg_replace( '(^([a-z]+).*)', '\\1', $dsn );

// Detect directories
if ( file_exists( 'eZ/Publish/Core/Persistence/' ) )
{
    $baseDir = "";
    $legacyHandlerDir = "eZ/Publish/Core/Persistence";
}
else if ( file_exists( 'vendor/ezsystems/ezpublish/eZ/Publish/Core/Persistence/' ) )
{
    $baseDir = "vendor/ezsystems/ezpublish/";
    $legacyHandlerDir = "vendor/ezsystems/ezpublish/eZ/Publish/Core/Persistence";
}
else
{
    throw new \Exception( 'Could not find Legacy dir, skipping' );
}

// get configuration config
if ( !( $settings = include ( $baseDir . 'config.php' ) ) )
{
    throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
}

// set existing services
$dependencies = array();
if ( isset( $_ENV['legacyKernel'] ) )
{
    $dependencies['@legacyKernel'] = $_ENV['legacyKernel'];
}

// load configuration uncached
$configManager = new ConfigurationManager(
    array_merge_recursive( $settings, array(
        'base' => array(
            'Configuration' => array(
                'UseCache' => false
            )
        )
    ) ),
    $settings['base']['Configuration']['Paths']
);

// load service container & configuration, but force legacy handler
$serviceSettings = $configManager->getConfiguration('service')->getAll();
$serviceSettings['repository']['arguments']['persistence_handler'] = '@persistence_handler_legacy';
$serviceSettings['repository']['arguments']['io_handler'] = '@io_handler_legacy';
$serviceSettings['persistence_handler_legacy']['arguments']['config']['dsn'] = $dsn;
$sc = new ServiceContainer(
    $serviceSettings,
    $dependencies
);


$legacyHandler = $sc->get( 'persistence_handler_legacy' );

// Get access to ezc DB handler
$refGetDatabase = new ReflectionMethod( $legacyHandler, 'getDatabase' );
$refGetDatabase->setAccessible( true );
$handler = $refGetDatabase->invoke( $legacyHandler );


// Insert Schema
$schema = $legacyHandlerDir . '/Legacy/Tests/_fixtures/schema.' . $db . '.sql';
$queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $schema ) ) );
foreach ( $queries as $query )
{
    $handler->exec( $query );
}


// Insert some default data
$data = require __DIR__ . '/_fixtures/full_dump.php';
//$data = require __DIR__ . '/_fixtures/mini_dump.php';
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


return $sc->getRepository();
