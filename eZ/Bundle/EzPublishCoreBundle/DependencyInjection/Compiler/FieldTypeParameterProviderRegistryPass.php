<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Publish\Core\Base\Container\Compiler\TaggedServiceIdsIterator\BackwardCompatibleIterator;
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

        $iterator = new BackwardCompatibleIterator(
            $container,
            self::FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG,
            self::DEPRECATED_FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG
        );

        foreach ($iterator as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new \LogicException(
                        sprintf(
                            '%s or %s service tag needs an "alias" attribute to identify the Field Type.',
                            self::DEPRECATED_FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG,
                            self::FIELD_TYPE_PARAMETER_PROVIDER_SERVICE_TAG
                        )
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
