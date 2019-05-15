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

        $ezpublishIndexableFieldTypeTags = $container->findTaggedServiceIds('ezpublish.fieldType.indexable');
        foreach ($ezpublishIndexableFieldTypeTags as $ezpublishIndexableFieldTypeTag) {
            @trigger_error('`ezpublish.fieldType.indexable` service tag is deprecated and will be removed in version 9. Please use `ezplatform.field_type.indexable`. instead.', E_USER_DEPRECATED);
        }
        $ezplatformIndexableFieldTypeTags = $container->findTaggedServiceIds('ezplatform.field_type.indexable');
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
