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
        if ( !isset( self::$serviceContainer ) )
        {
            $container = parent::getServiceContainer()->getInnerContainer();

            // disable cache - TODO fix bug with trash recover
            $container->setAlias(
                "ezpublish.api.persistence_handler",
                "ezpublish.spi.persistence.legacy"
            );
            // Reset changed language settings from parent factory
            $container->setParameter(
                "languages",
                array()
            );
        }

        return static::$serviceContainer;
    }
}
