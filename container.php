<?php
/**
 * File generates service container instance
 *
 * Expects global $settings to be set by caller
 *
 * @deprecated Since 5.0, this is only used for unit tests.
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

use eZ\Publish\Core\Base\ServiceContainer;

if ( !isset( $settings ) )
{
    throw new \RuntimeException( '$settings not provided to container.php' );
}

return new ServiceContainer(
    $settings['service']['parameters']['install_dir'],
    $settings['service']['parameters']['install_dir'] . "/eZ/Publish/Core/settings",
    $settings['service']['parameters']['install_dir'] . "/var/cache/container"
);
