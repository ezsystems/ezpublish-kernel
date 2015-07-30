<?php

/**
 * This file is part of the ezpublish-kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiles services tagged as ezplatform.installer to %ezplatform.installers%.
 */
class InstallerTagPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezplatform.installer.install_command')) {
            return;
        }

        $installCommandDef = $container->findDefinition('ezplatform.installer.install_command');
        $installers = [];

        foreach ($container->findTaggedServiceIds('ezplatform.installer') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \LogicException("ezplatform.installer service tag needs a 'type' attribute to identify the installer. None given for $id.");
                }

                $installers[$tag['type']] = new Reference($id);
            }
        }

        $installCommandDef->replaceArgument(1, $installers);
    }
}
