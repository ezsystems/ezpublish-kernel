<?php

/**
 * File containing the FieldTypeCollectionPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler;

use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\Core\FieldType\Null\Type;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register eZ Platform Field Types.
 */
class FieldTypeRegistryPass extends AbstractFieldTypeBasedPass
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(FieldTypeRegistry::class)) {
            return;
        }

        $fieldTypeRegistryDefinition = $container->getDefinition(FieldTypeRegistry::class);

        foreach ($this->getFieldTypeServiceIds($container) as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $fieldTypeRegistryDefinition->addMethodCall(
                    'registerFieldType',
                    [
                        $attribute['alias'],
                        new Reference($id),
                    ]
                );

                // Add FieldType to the "concrete" list if it's not a fake.
                if (!is_a($container->findDefinition($id)->getClass(), Type::class, true)) {
                    $fieldTypeRegistryDefinition->addMethodCall(
                        'registerConcreteFieldTypeIdentifier',
                        [$attribute['alias']]
                    );
                }
            }
        }
    }
}
