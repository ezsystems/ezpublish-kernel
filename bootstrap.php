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
    eZ\Publish\Core\Base\ServiceContainer,
    eZ\Publish\Legacy\Kernel as LegacyKernel,
    eZ\Publish\Legacy\Kernel\CLIHandler as LegacyKernelCLI;

if ( !( $settings = include ( __DIR__ . '/config.php' ) ) )
{
    throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
}

// Auto-detect eZ Publish 5.x context with eZ Publish 4.x available.
$baseDir = __DIR__;
$legacyDir = null;
if ( file_exists( "{$baseDir}/vendor/ezsystems/ezpublish-legacy" ) )
{
//    $baseDir = str_replace( '/vendor/ezsystems/ezpublish', '', __DIR__ );
    $legacyDir = "{$baseDir}/vendor/ezsystems/ezpublish-legacy";
}

// Setup class loader using composer maps
if ( !file_exists( "{$baseDir}/vendor/composer/autoload_namespaces.php" ) )
{
    throw new \RuntimeException( 'Install dependencies with composer to run the test suites' );
}

require __DIR__ . '/eZ/Publish/Core/Base/ClassLoader.php';
$classLoader = new ClassLoader(
    include "{$baseDir}/vendor/composer/autoload_namespaces.php",
    include "{$baseDir}/vendor/composer/autoload_classmap.php",
    $legacyDir
);
spl_autoload_register( array( $classLoader, 'load' ) );

// Bootstrap eZ Publish legacy kernel if configured
$dependencies = array();
if ( $legacyDir )
{
    $legacyKernel = new LegacyKernel( new LegacyKernelCLI, $legacyDir, getcwd() );
    set_exception_handler( null );
    // Avoid "Fatal error" text from legacy kernel if not called
    $legacyKernel->runCallback(
        function ()
        {
            eZExecution::setCleanExit();
        }
    );

    // Exposing in env variables in order be able to use them in test cases.
    $_ENV['legacyKernel'] = $legacyKernel;
    $_ENV['legacyPath'] = $legacyDir;

    $dependencies['@legacyKernel'] = $legacyKernel;
}

$configManager = new ConfigurationManager(
    $settings,
    $settings['base']['Configuration']['Paths']
);

$sc = new ServiceContainer(
    $configManager->getConfiguration('service')->getAll(),
    $dependencies
);

return $sc;
