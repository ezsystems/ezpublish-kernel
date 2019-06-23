<?php

/**
 * File containing the ValueObjectVisitorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for the ezpublish_rest.output.value_object_visitor tag.
 * Maps an fully qualified class to a value object visitor.
 */
class ValueObjectVisitorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish_rest.output.value_object_visitor.dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('ezpublish_rest.output.value_object_visitor.dispatcher');

        foreach ($container->findTaggedServiceIds('ezpublish_rest.output.value_object_visitor') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['type'])) {
                    throw new \LogicException('ezpublish_rest.output.value_object_visitor service tag needs a "type" attribute to identify the field type. None given.');
                }

                $definition->addMethodCall(
                    'addVisitor',
                    [$attribute['type'], new Reference($id)]
                );
            }
        }
    }
}
