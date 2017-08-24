<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Decorator for \Doctrine\DBAL\Connection which injects custom Database Platform.
 */
class ConnectionFactory
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform[]
     */
    private $platforms;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create \Doctrine\DBAL\Connection with custom Database Platform.
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection()
    {
        $params = $this->connection->getParams();
        $configuration = $this->connection->getConfiguration();
        $eventManager = $this->connection->getEventManager();

        // if custom platform does not exist, Doctrine will create its own
        if (isset($this->platforms[$params['driver']])) {
            $params['platform'] = $this->platforms[$params['driver']];
        }

        return DriverManager::getConnection($params, $configuration, $eventManager);
    }

    /**
     * @param string $driver database driver name
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function registerDatabaseService($driver, AbstractPlatform $platform)
    {
        $this->platforms[$driver] = $platform;
    }
}
