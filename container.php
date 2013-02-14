<?php
/**
 * File generates service container instance
 *
 * Expects global $settings to be set by caller
 *
 * @deprecated Since 5.0, this is only used for unit tests.
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

use eZ\Publish\Core\Base\ConfigurationManager;
use eZ\Publish\Core\Base\ServiceContainer;

if ( !isset( $settings ) )
{
    throw new \RuntimeException( '$settings not provided to container.php' );
}

// Setup Configuration object to be able to read service.ini settings
$configManager = new ConfigurationManager(
    $settings,
    $settings['base']['Configuration']['Paths']
);

// [temp] Inject legacy kernel, as it does not yet have a factory
$dependencies = array();
if ( isset( $_ENV['legacyKernel'] ) )
{
    $dependencies['@legacyKernel'] = $_ENV['legacyKernel'];
}

// Return Service container with service.ini settings
return new ServiceContainer(
    $configManager->getConfiguration( 'service' )->getAll(),
    $dependencies
);
