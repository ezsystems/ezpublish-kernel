<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer\DatabasePlatform;

use EzSystems\PlatformInstallerBundle\Installer\DbBasedInstaller;
use EzSystems\PlatformInstallerBundle\Installer\Installer;

class MySqlCleanInstaller extends DbBasedInstaller implements Installer
{
    public function createConfiguration()
    {
    }

    public function importSchema()
    {
        $this->runQueriesFromFile(realpath(dirname(__DIR__) . '/../../../../../data/mysql/schema.sql'));
        $this->runQueriesFromFile(realpath(dirname(__DIR__) . '/../../../../../data/mysql/dfs_schema.sql'));
    }

    public function importData()
    {
        $this->runQueriesFromFile(
            realpath(dirname(__DIR__) . '/../../../../../data/mysql/cleandata.sql')
        );
    }

    public function importBinaries()
    {
    }
}
