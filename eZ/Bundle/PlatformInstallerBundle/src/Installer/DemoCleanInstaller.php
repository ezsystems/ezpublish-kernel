<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

class DemoCleanInstaller extends DbBasedInstaller implements Installer
{
    public function importSchema()
    {
        $this->runQueriesFromFile(
            'vendor/ezsystems/ezpublish-kernel/data/mysql/schema.sql'
        );
    }

    public function importData()
    {
        $this->runQueriesFromFile(
            'vendor/ezsystems/ezpublish-kernel/data/demo_clean_data.sql'
        );
    }

    public function createConfiguration()
    {
        $this->copyConfigurationFile(
            __DIR__ . '/../Resources/config_templates/demo/ezpublish.yml',
            'ezpublish/config/ezpublish.yml'
        );

        $this->copyConfigurationFile(
            __DIR__ . '/../Resources/config_templates/common/ezpublish_dev.yml',
            'ezpublish/config/ezpublish_dev.yml'
        );

        $this->copyConfigurationFile(
            __DIR__ . '/../Resources/config_templates/common/ezpublish_prod.yml',
            'ezpublish/config/ezpublish_prod.yml'
        );
    }

    public function importBinaries()
    {
    }
}
