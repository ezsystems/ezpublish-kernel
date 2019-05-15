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

        $ezpublishFieldTypeParameterProviderTags = $container->findTaggedServiceIds('ezpublish.fieldType.parameterProvider');
        foreach ($ezpublishFieldTypeParameterProviderTags as $ezpublishFieldTypeParameterProviderTag) {
            @trigger_error('`ezpublish.fieldType.parameterProvider` service tag is deprecated and will be removed in version 9. Please use `ezplatform.field_type.parameter_provider` instead.', E_USER_DEPRECATED);
        }
        $ezplatformFieldTypeParameterProviderTags = $container->findTaggedServiceIds('ezplatform.field_type.parameter_provider');
        $parameterProviderFieldTypesTags = array_merge($ezpublishFieldTypeParameterProviderTags, $ezplatformFieldTypeParameterProviderTags);
        foreach ($parameterProviderFieldTypesTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new \LogicException(
                        'ezpublish.fieldType.parameterProvider or ezplatform.field_type.parameter_provider service tag needs an "alias" ' .
                        'attribute to identify the field type. None given.'
                    );
                }

                $parameterProviderRegistryDef->addMethodCall(
                    'setParameterProvider',
                    array(
                        // Only pass the service Id since field types will be lazy loaded via the service container
                        new Reference($id),
                        $attribute['alias'],
                    )
                );
            }
        }
    }
}
