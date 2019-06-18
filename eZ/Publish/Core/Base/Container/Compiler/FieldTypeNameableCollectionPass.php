<?php

/**
 * File containing the FieldTypeCollectionPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;

/**
 * This compiler pass will register eZ Publish nameable field types.
 */
class FieldTypeNameableCollectionPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.field_type_collection.factory')) {
            return;
        }

        $fieldTypeCollectionFactoryDef = $container->getDefinition('ezpublish.field_type_nameable_collection.factory');
        $nameableFieldTypes = [];

        // Nameable Field types.
        // Alias attribute is the field type string.
        foreach ($container->findTaggedServiceIds('ezpublish.fieldType.nameable') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        'ezpublish.fieldType service tag needs an "alias" attribute to identify the field type. None given.'
                    );
                }

                $fieldTypeCollectionFactoryDef->addMethodCall(
                    'registerNameableFieldType',
                    [
                        // Only pass the service Id since field types will be lazy loaded via the service container
                        $id,
                        $attribute['alias'],
                    ]
                );

                $nameableFieldTypes[] = $attribute['alias'];
            }
        }

        // Field types, loop over and detect those that are missing nameable service.
        // Alias attribute is the field type string.
        foreach ($container->findTaggedServiceIds('ezpublish.fieldType') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        'ezpublish.fieldType service tag needs an "alias" attribute to identify the field type. None given.'
                    );
                }

                if (in_array($attribute['alias'], $nameableFieldTypes)) {
                    continue;
                }

                $fieldTypeCollectionFactoryDef->addMethodCall(
                    'registerNonNameableFieldType',
                    [
                        // Only pass the service Id since field types will be lazy loaded via the service container
                        $id,
                        $attribute['alias'],
                    ]
                );
            }
        }
    }
}
