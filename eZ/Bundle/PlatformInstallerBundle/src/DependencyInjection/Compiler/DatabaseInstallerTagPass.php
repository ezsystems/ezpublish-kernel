<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiles services tagged as ezplatform.installer.database to
 * %ezplatform.installer.database.connection.factory%.
 */
class DatabaseInstallerTagPass implements CompilerPassInterface
{
    const DEFINITION_ID = 'ezplatform.installer.database.connection.factory';

    const TAG_ID = 'ezplatform.installer.database';

    /**
     * Process services tagged as ezplatform.installer.database.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::DEFINITION_ID)) {
            return;
        }

        $definition = $container->findDefinition(self::DEFINITION_ID);
        foreach ($container->findTaggedServiceIds(self::TAG_ID) as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['driver'])) {
                    throw new \LogicException(
                        sprintf(
                            '%s service tag needs a \'driver\' attribute to identify the database driver. None given for %s',
                            self::TAG_ID,
                            $id
                        )
                    );
                }

                $definition->addMethodCall(
                    'registerDatabaseService',
                        [
                            $tag['driver'],
                            new Reference($id),
                        ]
                );
            }
        }
    }
}
