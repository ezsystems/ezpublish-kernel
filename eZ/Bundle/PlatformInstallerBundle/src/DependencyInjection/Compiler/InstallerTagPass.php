<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler;

use EzSystems\PlatformInstallerBundle\Command\InstallPlatformCommand;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiles services tagged as ezplatform.installer to %ezplatform.installers%.
 */
class InstallerTagPass implements CompilerPassInterface
{
    public const INSTALLER_TAG = 'ezplatform.installer';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(InstallPlatformCommand::class)) {
            return;
        }

        $installCommandDef = $container->findDefinition(InstallPlatformCommand::class);
        $installers = [];

        foreach ($container->findTaggedServiceIds(self::INSTALLER_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new LogicException(
                        sprintf(
                            'Service tag %s needs a "type" attribute to identify the installer. You need to provide a tag for %s.',
                            self::INSTALLER_TAG,
                            $id
                        )
                    );
                }

                $installers[$tag['type']] = new Reference($id);
            }
        }

        $installCommandDef->replaceArgument('$installers', $installers);
    }
}
