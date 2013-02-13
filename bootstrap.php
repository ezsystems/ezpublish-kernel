<?php
/**
 * File containing the bootstrapping of eZ Publish API for unit test use
 *
 * Setups class loading.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use eZ\Publish\Core\MVC\Legacy\Kernel\CLIHandler as LegacyKernelCLI;

// Get globl config.php settings
if ( !( $settings = include ( __DIR__ . '/config.php' ) ) )
{
    throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php & customize to your needs!' );
}

// Setup class loader, detect ezpublish-community repo context and use vendor files from there if that is the case
$rootDir = __DIR__;
if ( ( $vendorPathPos = strrpos( $rootDir, '/vendor/ezsystems/ezpublish' ) ) !== false )
    $rootDir = substr( $rootDir, 0, $vendorPathPos );
require_once $rootDir . "/vendor/autoload.php";


// Bootstrap eZ Publish legacy kernel if configured
if ( !empty( $settings['service']['parameters']['legacy_dir'] ) )
{
    if ( !defined( 'EZCBASE_ENABLED' ) )
    {
        define( 'EZCBASE_ENABLED', false );
        require_once $settings['service']['parameters']['legacy_dir'] . '/autoload.php';
    }

    // Define $legacyKernelHandler to whatever you need before loading this bootstrap file.
    // CLI handler is used by defaut, but you must use \ezpKernelWeb if not in CLI context (i.e. REST server)
    // $legacyKernelHandler can be a closure returning the appropriate kernel handler (to avoid autoloading issues)
    if ( isset( $legacyKernelHandler ) )
    {
        $legacyKernelHandler = $legacyKernelHandler instanceof \Closure ? $legacyKernelHandler() : $legacyKernelHandler;
    }
    else
    {
        $legacyKernelHandler = new LegacyKernelCLI;
    }
    $legacyKernel = new LegacyKernel( $legacyKernelHandler, $settings['service']['parameters']['legacy_dir'], getcwd() );
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
    $_ENV['legacyPath'] = $settings['service']['parameters']['legacy_dir'];
}

return include 'container.php';
