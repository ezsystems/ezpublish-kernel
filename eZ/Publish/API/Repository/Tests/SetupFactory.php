<?php

/**
 * File containing the Test Setup Factory base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
abstract class SetupFactory
{
    /**
     * Returns a configured repository for testing.
     *
     * @param bool $initializeFromScratch if the back end should be initialized
     *                                    from scratch or re-used
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    abstract public function getRepository($initializeFromScratch = true);

    /**
     * Returns a repository specific ID manager.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    abstract public function getIdManager();

    /**
     * Returns a config value for $configKey.
     *
     * @param string $configKey
     *
     * @throws Exception if $configKey could not be found.
     *
     * @return mixed
     */
    abstract public function getConfigValue($configKey);

    /**
     * Returns the service container used for initialization of the repository.
     *
     * Most tests should not use this at all!!
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    abstract public function getServiceContainer();
}
