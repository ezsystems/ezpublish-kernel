<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Integration;

use eZ\Publish\API\Repository\Tests\BaseTest as APIBaseTest;

/**
 * Base class for non-API Field Type integration tests (like Gateway w/ DBMS integration).
 */
abstract class BaseCoreFieldTypeIntegrationTest extends APIBaseTest
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (!isset($_ENV['setupFactory'])) {
            self::markTestSkipped(
                static::class . ' is an integration test and requires setupFactory env setting. '
                . 'Use phpunit-integration-legacy.xml configuration to run it'
            );
        }
    }

    /**
     * Return the database handler from the service container.
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler|object
     */
    protected function getDatabaseHandler()
    {
        return $this->getSetupFactory()->getServiceContainer()->get(
            'ezpublish.api.storage_engine.legacy.dbhandler'
        );
    }

    /**
     * Return the database connection from the service container.
     *
     * @return \Doctrine\DBAL\Connection|object
     */
    protected function getDatabaseConnection()
    {
        return $this->getSetupFactory()->getServiceContainer()->get(
            'ezpublish.api.storage_engine.legacy.connection'
        );
    }
}
