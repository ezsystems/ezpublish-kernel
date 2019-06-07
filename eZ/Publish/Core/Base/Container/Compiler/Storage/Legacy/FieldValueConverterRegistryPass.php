<?php

/**
 * File containing the FieldValueConverterRegistryPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Legacy Storage field value converters.
 */
class FieldValueConverterRegistryPass implements CompilerPassInterface
{
    public const STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG = 'ezplatform.field_type.legacy_storage.converter';
    public const DEPRECATED_STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG = 'ezpublish.storageEngine.legacy.converter';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.persistence.legacy.field_value_converter.registry')) {
            return;
        }

        $registry = $container->getDefinition('ezpublish.persistence.legacy.field_value_converter.registry');

        $deprecatedFieldTypeStorageConverterTags = $container->findTaggedServiceIds(self::DEPRECATED_STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG);
        foreach ($deprecatedFieldTypeStorageConverterTags as $deprecatedFieldTypeStorageConverterTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s` instead.',
                    self::DEPRECATED_STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG,
                    self::STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG
                ),
                E_USER_DEPRECATED
            );
        }
        $fieldTypeStorageConverterTags = $container->findTaggedServiceIds(self::STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG);
        $storageConverterFieldTypesTags = array_merge($deprecatedFieldTypeStorageConverterTags, $fieldTypeStorageConverterTags);
        foreach ($storageConverterFieldTypesTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            '%s or %s service tag needs an "alias" attribute to identify the field type. None given.',
                            self::DEPRECATED_STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG,
                            self::STORAGE_ENGINE_LEGACY_CONVERTER_SERVICE_TAG
                        )
                    );
                }

                $registry->addMethodCall(
                    'register',
                    array(
                        $attribute['alias'],
                        new Reference($id),
                    )
                );
            }
        }
    }
}
