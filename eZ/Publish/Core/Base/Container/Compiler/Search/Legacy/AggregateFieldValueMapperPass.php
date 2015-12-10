<?php

/**
 * File containing the AggregateFieldValueMapperPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register legacy search engine field value mappers.
 */
class AggregateFieldValueMapperPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition(
                'ezpublish.search.legacy.content.field_value_mapper.aggregate'
            )
        ) {
            return;
        }

        $aggregateFieldValueMapperDefinition = $container->getDefinition(
            'ezpublish.search.legacy.content.field_value_mapper.aggregate'
        );

        $taggedServiceIds = $container->findTaggedServiceIds(
            'ezpublish.search.legacy.content.field_value_mapper'
        );
        foreach ($taggedServiceIds as $id => $attributes) {
            $aggregateFieldValueMapperDefinition->addMethodCall(
                'addMapper',
                [new Reference($id)]
            );
        }
    }
}
