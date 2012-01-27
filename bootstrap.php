<?php
/**
 * File containing the bootstrapping of ezp
 *
 * Returns instance of Service Container setup with configuration service and setups autoloader.
 *
 * @copyright Copyright (C) 2012 andrerom & eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


use ezp\Base\ClassLoader,
    ezp\Base\ConfigurationManager,
    ezp\Base\ServiceContainer;

// Setup autoloaders
if ( !( $settings = include( __DIR__ . '/config.php' ) ) )
{
    die( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
}

require __DIR__ . '/ezp/Base/ClassLoader.php';
$loader = new ClassLoader( $settings['base']['ClassLoader']['Repositories'], ClassLoader::MODE_PSR_0_STRICT );
spl_autoload_register( array( $loader, 'load' ) );

// Zeta Components
require $settings['base']['ClassLoader']['ezcBase'];
spl_autoload_register( array( 'ezcBase', 'autoload' ) );


$configManager = new ConfigurationManager(
    $settings
);

// Access matching before we get active modules or opposite?
// anyway access matching should use event filters hence be optional.

// Setup configuration for modules
$paths = array();
foreach ( $settings['base']['ClassLoader']['Repositories'] as $ns => $nsPath )
{
    foreach ( glob( "{$nsPath}/*", GLOB_ONLYDIR ) as $path )//@todo Take from configuration
    {
        $paths[] = "{$path}/settings/";
    }
}

$configManager->setGlobalDirs( $paths, 'modules' );

$sc = new ServiceContainer(
    $configManager->getConfiguration('service')->getAll(),
    array(
        '@classLoader' => $loader,
        '@configuration' => $configManager,
    )
);

// Temp stuff for unit tests
ServiceContainer::$instance = $sc;

return $sc;
