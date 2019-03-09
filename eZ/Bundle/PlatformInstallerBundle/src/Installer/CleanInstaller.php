<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

/**
 * @deprecated since 7.5. Use CoreInstaller instead.
 */
class CleanInstaller extends DbBasedInstaller implements Installer
{
    public function createConfiguration()
    {
    }

    public function importSchema()
    {
        @trigger_error(
            'CleanInstaller is deprecated since 7.5 and will be removed in 8.0. Use CoreInstaller instead.',
            E_USER_DEPRECATED
        );

        $this->runQueriesFromFile($this->getKernelSQLFileForDBMS('schema.sql'));
        $this->runQueriesFromFile($this->getKernelSQLFileForDBMS('dfs_schema.sql'));
    }

    public function importData()
    {
        $this->runQueriesFromFile($this->getKernelSQLFileForDBMS('cleandata.sql'));
    }

    public function importBinaries()
    {
    }
}
