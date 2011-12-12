<?php
/**
 * File containing the bootstrapping for PHPUnit
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


use ezp\Base\Autoloader,
    ezp\Base\Configuration;

// setup autoloaders
require __DIR__ . '/config.php';
require __DIR__ . '/ezp/Base/Autoloader.php';
spl_autoload_register( array( new Autoloader( $settings['base']['autoload'] ), 'load' ) );

// setup configuration
$paths = array();
foreach ( $settings['base']['autoload']['repositories'] as $ns => $nsPath )
{
    foreach ( glob( "{$nsPath}/*", GLOB_ONLYDIR ) as $path )//@todo Take from configuration
    {
        $paths[] = "{$path}/settings/";
    }
}
Configuration::setGlobalConfigurationData( $settings );
Configuration::setGlobalDirs( $paths, 'modules' );

unset( $paths, $path, $settings );
