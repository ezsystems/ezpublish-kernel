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
    public const EZPUBLISH_FIELD_TYPE_INDEXABLE = 'ezpublish.fieldType.indexable';
    public const EZPLATFORM_FIELD_TYPE_INDEXABLE = 'ezplatform.field_type.indexable';

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

        $ezpublishIndexableFieldTypeTags = $container->findTaggedServiceIds(self::EZPUBLISH_FIELD_TYPE_INDEXABLE);
        foreach ($ezpublishIndexableFieldTypeTags as $ezpublishIndexableFieldTypeTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s`. instead.',
                    self::EZPUBLISH_FIELD_TYPE_INDEXABLE,
                    self::EZPLATFORM_FIELD_TYPE_INDEXABLE
                ),
                E_USER_DEPRECATED
            );
        }
        $ezplatformIndexableFieldTypeTags = $container->findTaggedServiceIds(self::EZPLATFORM_FIELD_TYPE_INDEXABLE);
        $fieldTypesTags = array_merge($ezpublishIndexableFieldTypeTags, $ezplatformIndexableFieldTypeTags);
        foreach ($fieldTypesTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        'ezpublish.fieldType.indexable or ezplatform.field_type.indexable service tag needs an "alias" attribute to ' .
                        'identify the indexable field type. None given.'
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
