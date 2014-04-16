<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy;

use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as APILegacySetupFactory;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class SetupFactory extends APILegacySetupFactory
{
    /**
     * @var string
     */
    protected $repositoryReference = "ezpublish.api.inner_repository";

    /**
     * Returns the service container used for initialization of the repository
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    protected function getServiceContainer()
    {
        if ( !isset( static::$serviceContainer ) )
        {
            $config = include __DIR__ . "/../../../../../../../../config.php";
            $installDir = $config['install_dir'];

            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
            $containerBuilder = include $installDir . "/eZ/Publish/Core/settings" . "/container_builder.php";

            // disable cache - TODO fix bug with trash recover
            $containerBuilder->setAlias(
                "ezpublish.api.persistence_handler",
                "ezpublish.spi.persistence.legacy"
            );
            $containerBuilder->setParameter(
                "languages",
                array()
            );
            $containerBuilder->setParameter(
                "legacy_dsn",
                static::$dsn
            );

            static::$serviceContainer = new ServiceContainer(
                $installDir,
                $config['settings_dir'],
                $config['cache_dir'],
                true,
                $containerBuilder
            );
        }

        return static::$serviceContainer;
    }
}
