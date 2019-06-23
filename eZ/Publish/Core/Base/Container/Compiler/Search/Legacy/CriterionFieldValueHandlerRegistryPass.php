<?php

/**
 * File containing the CriterionFieldValueHandlerRegistryPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Legacy Search Engine criterion field value handlers.
 */
class CriterionFieldValueHandlerRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.search.legacy.gateway.criterion_field_value_handler.registry')) {
            return;
        }

        $registry = $container->getDefinition('ezpublish.search.legacy.gateway.criterion_field_value_handler.registry');

        foreach ($container->findTaggedServiceIds('ezpublish.search.legacy.gateway.criterion_field_value_handler') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        'ezpublish.search.legacy.gateway.criterion_field_value_handler service tag needs an "alias" attribute to identify the field type. None given.'
                    );
                }

                $registry->addMethodCall(
                    'register',
                    [
                        $attribute['alias'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
