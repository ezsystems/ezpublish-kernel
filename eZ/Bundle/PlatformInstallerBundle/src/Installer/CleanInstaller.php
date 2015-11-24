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
        $this->runQueriesFromFile(
            'vendor/ezsystems/ezpublish-kernel/data/mysql/schema.sql'
        );
    }

    public function importData()
    {
        $this->runQueriesFromFile(
            'vendor/ezsystems/ezpublish-kernel/data/cleandata.sql'
        );
    }

    public function importBinaries()
    {
    }
}
