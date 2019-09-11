<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle;

use EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\InstallerTagPass;
use EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\SchemaBuilderInstallerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzSystemsPlatformInstallerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SchemaBuilderInstallerPass());
        $container->addCompilerPass(new InstallerTagPass());
    }
}
