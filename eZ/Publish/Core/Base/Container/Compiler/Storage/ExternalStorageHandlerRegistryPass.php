<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Storage;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

/**
 * This compiler pass will register external storage persistence handlers.
 */
class ExternalStorageHandlerRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        $storageHandlerRegistryServiceId = 'ezpublish.persistence.external_storage_handler.registry';
        $storageHandlerRegistryServiceTagName = 'ezpublish.persistence.externalStorageHandler';
        if (!$container->hasDefinition($storageHandlerRegistryServiceId)) {
            return;
        }

        // Register Persistence Layer-specific External Storage Handlers (for Doctrine, Legacy, etc.)
        $storageHandlerRegistry = $container->getDefinition($storageHandlerRegistryServiceId);
        foreach ($container->findTaggedServiceIds($storageHandlerRegistryServiceTagName) as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['identifier'])) {
                    throw new LogicException("$storageHandlerRegistryServiceTagName service tag needs an 'identifier' attribute to identify the Storage. None given.");
                }

                $storageHandlerRegistry->addMethodCall(
                    'register',
                    [
                        $attribute['identifier'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
