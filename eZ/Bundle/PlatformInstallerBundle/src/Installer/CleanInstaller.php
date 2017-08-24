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
        $this->importSchemaFromYaml(dirname(__DIR__) . '/../../../../data/schema.yml');
    }

    public function importData()
    {
        $databasePlatform = $this->db->getDatabasePlatform();
        $databasePlatformName = $databasePlatform->getName();
        $this->runQueriesFromFile(
            realpath(dirname(__DIR__) . "/../../../../data/$databasePlatformName/cleandata.sql")
        );

        if ($databasePlatform->supportsSequences()) {
            $this->alignSequences();
        }
    }

    public function importBinaries()
    {
    }
}
