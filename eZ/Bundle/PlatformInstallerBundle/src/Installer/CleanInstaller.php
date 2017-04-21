<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

class CleanInstaller extends DbBasedInstaller implements Installer
{
    public function createConfiguration()
    {
    }

    public function importSchema()
    {
        $this->runQueriesFromFile('vendor/ezsystems/ezpublish-kernel/data/mysql/schema.sql');
        $this->runQueriesFromFile('vendor/ezsystems/ezpublish-kernel/data/mysql/dfs_schema.sql');
    }

    public function importData()
    {
        $databasePlatform = $this->db->getDatabasePlatform()->getName();
        $this->runQueriesFromFile(
            realpath(dirname(__DIR__) . "/../../../../data/$databasePlatform/cleandata.sql")
        );
    }

    public function importBinaries()
    {
    }
}
