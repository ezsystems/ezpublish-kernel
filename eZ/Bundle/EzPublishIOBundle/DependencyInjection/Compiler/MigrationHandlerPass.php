<?php

/**
 * File containing the MigrationHandlerPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MigrationHandlerPass implements CompilerPassInterface
{
    /**
     * Registers the MigrationHandlerInterface into the migration handler registry.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish.core.io.migration.migration_handler_registry')) {
            return;
        }

        $migrationHandlersTagged = $container->findTaggedServiceIds('ezpublish.core.io.migration.migration_handler');

        $migrationHandlers = [];
        foreach ($migrationHandlersTagged as $id => $tags) {
            foreach ($tags as $attributes) {
                $migrationHandlers[$attributes['identifier']] = new Reference($id);
            }
        }

        $migrationHandlerRegistryDef = $container->findDefinition('ezpublish.core.io.migration.migration_handler_registry');
        $migrationHandlerRegistryDef->setArguments([$migrationHandlers]);
    }
}
