<?php

/**
 * File containing the FieldTypeParameterProviderRegistryPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register eZ Publish field type parameter providers.
 */
class FieldTypeParameterProviderRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.fieldType.parameterProviderRegistry')) {
            return;
        }

        $parameterProviderRegistryDef = $container->getDefinition('ezpublish.fieldType.parameterProviderRegistry');

        foreach ($container->findTaggedServiceIds('ezpublish.fieldType.parameterProvider') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new \LogicException(
                        'ezpublish.fieldType.parameterProvider service tag needs an "alias" ' .
                        'attribute to identify the field type. None given.'
                    );
                }

                $parameterProviderRegistryDef->addMethodCall(
                    'setParameterProvider',
                    [
                        // Only pass the service Id since field types will be lazy loaded via the service container
                        new Reference($id),
                        $attribute['alias'],
                    ]
                );
            }
        }
    }
}
