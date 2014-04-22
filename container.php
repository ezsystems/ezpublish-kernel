<?php
/**
 * File generates service container instance
 *
 * Expects global $config to be set by caller
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

use eZ\Publish\Core\Base\ServiceContainer;

if ( !isset( $config ) )
{
    throw new \RuntimeException( '$config not provided to container.php' );
}

return new ServiceContainer(
    $config['container_builder_path'],
    $config['install_dir'],
    $config['cache_dir']
);
