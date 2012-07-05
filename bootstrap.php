<?php
/**
 * File containing the bootstrapping of eZ Publish Next
 *
 * Returns instance of Service Container setup with configuration service and setups autoloader.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


use eZ\Publish\Core\Base\ClassLoader,
    eZ\Publish\Core\Base\ConfigurationManager,
    eZ\Publish\Core\Base\ServiceContainer;

// Setup autoloaders
if ( !( $settings = include ( __DIR__ . '/config.php' ) ) )
{
    throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
}

// Generic composer autoloader
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if ( !file_exists( $autoloadFile ) )
    throw new \RuntimeException( 'Install dependencies with composer to run the test suites' );
include $autoloadFile;

// Autoloader for eZ Publish legacy
if ( isset( $settings['base']['Legacy']['RootPath'] ) )
{
    $legacyPath = $settings['base']['Legacy']['RootPath'];
    // Deactivate eZComponents loading from legacy autoload.php as they are already loaded with Composer
    if ( !defined( 'EZCBASE_ENABLED' ) )
        define( 'EZCBASE_ENABLED', false );
    require "$legacyPath/autoload.php";
}
else
{
    throw new \RuntimeException( 'Please define your eZ Publish legacy root path in config.php' );
}

$configManager = new ConfigurationManager(
    $settings,
    $settings['base']['Configuration']['Paths']
);

// Access matching before we get active modules or opposite?
// anyway access matching should use event filters hence be optional.

// Setup configuration for modules
/*$paths = array();
foreach ( $settings['base']['ClassLoader']['Repositories'] as $ns => $nsPath )
{
    foreach ( glob( "{$nsPath}/*", GLOB_ONLYDIR ) as $path )//@todo Take from configuration
    {
        $paths[] = "{$path}/settings/";
    }
}

$configManager->setGlobalDirs( $paths, 'modules' );*/

$loader = new ClassLoader( array() );
$sc = new ServiceContainer(
    $configManager->getConfiguration('service')->getAll(),
    array(
         '$classLoader' => $loader,
         '$configurationManager' => $configManager,
    )
);

return $sc;
