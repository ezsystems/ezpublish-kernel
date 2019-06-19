<?php

/**
 * File containing the FieldRegistryPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

/**
 * This compiler pass will register eZ Publish indexable field types.
 */
class FieldRegistryPass implements CompilerPassInterface
{
    public const FIELD_TYPE_INDEXABLE_SERVICE_TAG = 'ezplatform.field_type.indexable';
    public const DEPRECATED_FIELD_TYPE_INDEXABLE_SERVICE_TAG = 'ezpublish.fieldType.indexable';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.search.common.field_registry')) {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition('ezpublish.search.common.field_registry');

        $deprecatedIndexableFieldTypeTags = $container->findTaggedServiceIds(self::DEPRECATED_FIELD_TYPE_INDEXABLE_SERVICE_TAG);
        foreach ($deprecatedIndexableFieldTypeTags as $deprecatedIndexableFieldTypeTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s`. instead.',
                    self::DEPRECATED_FIELD_TYPE_INDEXABLE_SERVICE_TAG,
                    self::FIELD_TYPE_INDEXABLE_SERVICE_TAG
                ),
                E_USER_DEPRECATED
            );
        }
        $indexableFieldTypeTags = $container->findTaggedServiceIds(self::FIELD_TYPE_INDEXABLE_SERVICE_TAG);
        $fieldTypesTags = array_merge($deprecatedIndexableFieldTypeTags, $indexableFieldTypeTags);
        foreach ($fieldTypesTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            '%s or %s service tag needs an "alias" attribute to identify the indexable field type. None given.',
                            self::DEPRECATED_FIELD_TYPE_INDEXABLE_SERVICE_TAG,
                            self::FIELD_TYPE_INDEXABLE_SERVICE_TAG
                        )
                    );
                }

                $fieldRegistryDefinition->addMethodCall(
                    'registerType',
                    array(
                        $attribute['alias'],
                        new Reference($id),
                    )
                );
            }
        }
    }
}
