<?php

/**
 * File containing the Test Setup Factory for the REST SDK.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests;

use eZ\Publish\Core\REST\Common;
use eZ\Publish\API\Repository;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class SetupFactory extends Repository\Tests\SetupFactory
{
    /**
     * Returns a configured repository for testing.
     *
     * @param bool $initializeFromScratch
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository($initializeFromScratch = true)
    {
        return require __DIR__ . '/../../common.php';
    }

    /**
     * Returns a repository specific ID manager.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    public function getIdManager()
    {
        return new IdManager(
            new Common\RequestParser\EzPublish()
        );
    }

    /**
     * Returns a config value for $configKey.
     *
     * @param string $configKey
     *
     * @throws \Exception if $configKey could not be found.
     *
     * @return mixed
     */
    public function getConfigValue($configKey)
    {
        throw new \RuntimeException('REST implementation does not support config.');
    }
}
