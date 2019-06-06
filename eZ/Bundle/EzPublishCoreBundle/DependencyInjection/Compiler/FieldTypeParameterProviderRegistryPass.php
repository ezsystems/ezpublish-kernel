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
    public const EZPUBLISH_FIELD_TYPE_PARAMETER_PROVIDER = 'ezpublish.fieldType.parameterProvider';
    public const EZPLATFORM_FIELD_TYPE_PARAMETER_PROVIDER = 'ezplatform.field_type.parameter_provider';

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

        $ezpublishFieldTypeParameterProviderTags = $container->findTaggedServiceIds(self::EZPUBLISH_FIELD_TYPE_PARAMETER_PROVIDER);
        foreach ($ezpublishFieldTypeParameterProviderTags as $ezpublishFieldTypeParameterProviderTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s` instead.',
                    self::EZPUBLISH_FIELD_TYPE_PARAMETER_PROVIDER,
                    self::EZPLATFORM_FIELD_TYPE_PARAMETER_PROVIDER
                ),
                E_USER_DEPRECATED
            );
        }
        $ezplatformFieldTypeParameterProviderTags = $container->findTaggedServiceIds(self::EZPLATFORM_FIELD_TYPE_PARAMETER_PROVIDER);
        $parameterProviderFieldTypesTags = array_merge($ezpublishFieldTypeParameterProviderTags, $ezplatformFieldTypeParameterProviderTags);
        foreach ($parameterProviderFieldTypesTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new \LogicException(
                        sprintf(
                            '%s or %s service tag needs an "alias" attribute to identify the field type. None given.',
                            self::EZPUBLISH_FIELD_TYPE_PARAMETER_PROVIDER,
                            self::EZPLATFORM_FIELD_TYPE_PARAMETER_PROVIDER
                        )
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
