<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as APILegacySetupFactory;
use eZ\Publish\Core\Base\ServiceContainer;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class SetupFactory extends APILegacySetupFactory
{
    /**
     * Service container
     *
     * @var \eZ\Publish\Core\Base\ServiceContainer
     */
    protected static $legacyServiceContainer;

    /**
     * Returns the service container used for initialization of the repository
     *
     * @todo Getting service container statically, too, would be nice
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    protected function getServiceContainer()
    {
        if ( !isset( static::$legacyServiceContainer ) )
        {
            $configManager = $this->getConfigurationManager();

            $serviceSettings = $configManager->getConfiguration( 'service' )->getAll();

            $serviceSettings['persistence_handler']['alias'] = 'persistence_handler_legacy';
            $serviceSettings['io_handler']['alias'] = 'io_handler_legacy';
            $serviceSettings['legacy_db_handler']['arguments']['dsn'] = self::$dsn;

            static::$legacyServiceContainer = new ServiceContainer(
                $serviceSettings,
                $this->getDependencyConfiguration()
            );
        }

        return static::$legacyServiceContainer;
    }
}
