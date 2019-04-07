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
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.persistence.legacy.field_value_converter.registry.inner')) {
            return;
        }

        $registry = $container->getDefinition('ezpublish.persistence.legacy.field_value_converter.registry.inner');

        foreach ($container->findTaggedServiceIds('ezpublish.storageEngine.legacy.converter') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException('ezpublish.storageEngine.legacy.converter service tag needs an "alias" attribute to identify the field type. None given.');
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
