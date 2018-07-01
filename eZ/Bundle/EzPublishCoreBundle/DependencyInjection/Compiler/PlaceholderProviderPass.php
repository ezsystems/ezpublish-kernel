<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PlaceholderProviderPass implements CompilerPassInterface
{
    const TAG_NAME = 'ezpublish.placeholder_provider';
    const REGISTRY_DEFINITION_ID = 'ezpublish.image_alias.imagine.placeholder_provider.registry';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_DEFINITION_ID)) {
            return;
        }

        $definition = $container->getDefinition(self::REGISTRY_DEFINITION_ID);
        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['type'])) {
                    throw new LogicException(self::TAG_NAME . ' service tag needs a "type" attribute to identify the placeholder provider type. None given.');
                }

                $definition->addMethodCall(
                    'addProvider',
                    [$attribute['type'], new Reference($id)]
                );
            }
        }
    }
}
