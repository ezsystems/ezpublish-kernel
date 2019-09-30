<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle;

use EzSystems\DoctrineSchemaBundle\DoctrineSchemaBundle;
use EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\InstallerTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzSystemsPlatformInstallerBundle extends Bundle
{
    /**
     * @throws \RuntimeException
     */
    public function build(ContainerBuilder $container)
    {
        if (!$container->hasExtension('ez_doctrine_schema')) {
            throw new RuntimeException(
                sprintf(
                    'eZ Platform Installer requires Doctrine Schema Bundle (enable %s)',
                    DoctrineSchemaBundle::class
                )
            );
        }

        parent::build($container);
        $container->addCompilerPass(new InstallerTagPass());
    }
}
