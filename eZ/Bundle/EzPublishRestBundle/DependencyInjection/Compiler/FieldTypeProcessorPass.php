<?php

/**
 * File containing the FieldTypeProcessorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldTypeProcessorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish_rest.field_type_processor_registry')) {
            return;
        }

        $definition = $container->getDefinition('ezpublish_rest.field_type_processor_registry');

        foreach ($container->findTaggedServiceIds('ezpublish_rest.field_type_processor') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new \LogicException('ezpublish_rest.field_type_processor service tag needs an "alias" attribute to identify the field type. None given.');
                }

                $definition->addMethodCall(
                    'registerProcessor',
                    [$attribute['alias'], new Reference($id)]
                );
            }
        }
    }
}
