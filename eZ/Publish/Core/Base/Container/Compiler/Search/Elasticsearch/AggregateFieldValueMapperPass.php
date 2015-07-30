<?php

/**
 * File containing the AggregateFieldValueMapperPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Elasticsearch Search Engine field value mappers.
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
                'ezpublish.search.elasticsearch.content.field_value_mapper.aggregate'
            )
        ) {
            return;
        }

        $aggregateFieldValueMapperDefinition = $container->getDefinition(
            'ezpublish.search.elasticsearch.content.field_value_mapper.aggregate'
        );

        $taggedServiceIds = $container->findTaggedServiceIds(
            'ezpublish.search.elasticsearch.content.field_value_mapper'
        );
        foreach ($taggedServiceIds as $id => $attributes) {
            $aggregateFieldValueMapperDefinition->addMethodCall(
                'addMapper',
                [new Reference($id)]
            );
        }
    }
}
