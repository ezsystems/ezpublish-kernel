<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

interface Installer
{
    public function importSchema();

    public function importData();

    public function createConfiguration();

    public function importBinaries();
}
