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
    public const FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG = 'ezplatform.field_type.parameter_provider';
    public const DEPRECATED_FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG = 'ezpublish.fieldType.parameterProvider';

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

        $deprecatedFieldTypeParameterProviderTags = $container->findTaggedServiceIds(self::DEPRECATED_FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG);
        foreach ($deprecatedFieldTypeParameterProviderTags as $deprecatedFieldTypeParameterProviderTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s` instead.',
                    self::DEPRECATED_FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG,
                    self::FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG
                ),
                E_USER_DEPRECATED
            );
        }
        $fieldTypeParameterProviderTags = $container->findTaggedServiceIds(self::FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG);
        $parameterProviderFieldTypesTags = array_merge($deprecatedFieldTypeParameterProviderTags, $fieldTypeParameterProviderTags);
        foreach ($parameterProviderFieldTypesTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new \LogicException(
                        sprintf(
                            '%s or %s service tag needs an "alias" attribute to identify the field type. None given.',
                            self::DEPRECATED_FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG,
                            self::FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG
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
