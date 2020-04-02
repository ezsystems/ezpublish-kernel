<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\IO\Adapter;

use eZ\Publish\Core\IO\IOConfigProvider;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\SPI\MVC\EventSubscriber\ConfigScopeChangeSubscriber;
use League\Flysystem\Adapter\Local;
use LogicException;

/**
 * @internal
 */
final class LocalAdapter extends Local implements ConfigScopeChangeSubscriber
{
    /** @var \eZ\Publish\Core\IO\IOConfigProvider */
    private $ioConfigProvider;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(IOConfigProvider $ioConfigProvider, ConfigResolverInterface $configResolver)
    {
        $this->ioConfigProvider = $ioConfigProvider;
        $this->configResolver = $configResolver;

        $filesPermissions = $this->configResolver->getParameter('io.permissions.files');
        $directoriesPermissions = $this->configResolver->getParameter('io.permissions.directories');

        parent::__construct(
            $this->ioConfigProvider->getRootDir(),
            LOCK_EX,
            Local::DISALLOW_LINKS,
            ['file' => ['public' => $filesPermissions], 'dir' => ['public' => $directoriesPermissions]]
        );
    }

    /**
     * Reconfigure Adapter due to SiteAccess change which implies
     * root dir and permissions could be different for new SiteAccess.
     */
    public function onConfigScopeChange(SiteAccess $siteAccess): void
    {
        $root = $this->ioConfigProvider->getRootDir();
        $root = is_link($root) ? realpath($root) : $root;
        $this->ensureDirectory($root);

        if (!is_dir($root) || !is_readable($root)) {
            throw new LogicException(sprintf('The root path %s is not readable.', $root));
        }

        $this->setPathPrefix($root);

        $filesPermissions = $this->configResolver->getParameter('io.permissions.files');
        $directoriesPermissions = $this->configResolver->getParameter('io.permissions.directories');

        $this->permissionMap = array_replace_recursive(
            static::$permissions,
            ['file' => ['public' => $filesPermissions], 'dir' => ['public' => $directoriesPermissions]]
        );
    }
}
