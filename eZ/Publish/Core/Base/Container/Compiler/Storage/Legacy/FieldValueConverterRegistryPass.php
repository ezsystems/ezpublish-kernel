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
    public const EZPUBLISH_STORAGE_ENGINE_LEGACY_CONVERTER = 'ezpublish.storageEngine.legacy.converter';
    public const EZPLATFORM_FIELD_TYPE_LEGACY_STORAGE_CONVERTER = 'ezplatform.field_type.legacy_storage.converter';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.persistence.legacy.field_value_converter.registry')) {
            return;
        }

        $registry = $container->getDefinition('ezpublish.persistence.legacy.field_value_converter.registry');

        $ezpublishFieldTypeStorageConverterTags = $container->findTaggedServiceIds(self::EZPUBLISH_STORAGE_ENGINE_LEGACY_CONVERTER);
        foreach ($ezpublishFieldTypeStorageConverterTags as $ezpublishFieldTypeStorageConverterTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s` instead.',
                    self::EZPUBLISH_STORAGE_ENGINE_LEGACY_CONVERTER,
                    self::EZPLATFORM_FIELD_TYPE_LEGACY_STORAGE_CONVERTER
                ),
                E_USER_DEPRECATED
            );
        }
        $ezplatformFieldTypeStorageConverterTags = $container->findTaggedServiceIds(self::EZPLATFORM_FIELD_TYPE_LEGACY_STORAGE_CONVERTER);
        $storageConverterFieldTypesTags = array_merge($ezpublishFieldTypeStorageConverterTags, $ezplatformFieldTypeStorageConverterTags);
        foreach ($storageConverterFieldTypesTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            '%s or %s service tag needs an "alias" attribute to identify the field type. None given.',
                            self::EZPUBLISH_STORAGE_ENGINE_LEGACY_CONVERTER,
                            self::EZPLATFORM_FIELD_TYPE_LEGACY_STORAGE_CONVERTER
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
