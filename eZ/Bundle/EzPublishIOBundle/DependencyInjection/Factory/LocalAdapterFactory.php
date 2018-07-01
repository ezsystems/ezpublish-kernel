<?php
/**
 * This file is part of the ezplatform package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Factory;

use League\Flysystem\Adapter\Local;

/**
 * Builds a Local Flysystem Adapter instance with the given permissions configuration.
 */
class LocalAdapterFactory
{
    /**
     * @param string $rootDir
     * @param int $filesPermissions Permissions used when creating files. Example: 0640.
     * @param int $directoriesPermissions Permissions when creating directories. Example: 0750.
     * @return Local
     */
    public function build($rootDir, $filesPermissions, $directoriesPermissions)
    {
        return new Local(
            $rootDir,
            LOCK_EX,
            Local::DISALLOW_LINKS,
            ['file' => ['public' => $filesPermissions], 'dir' => ['public' => $directoriesPermissions]]
        );
    }
}
