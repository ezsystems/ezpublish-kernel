<?php

/**
 * File containing the AggregateFacetBuilderVisitorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Elasticsearch Search Engine facet builder visitors.
 */
class AggregateFacetBuilderVisitorPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition(
                'ezpublish.search.elasticsearch.content.facet_builder_visitor.aggregate'
            )
        ) {
            return;
        }

        $aggregateFacetBuilderVisitorDefinition = $container->getDefinition(
            'ezpublish.search.elasticsearch.content.facet_builder_visitor.aggregate'
        );

        $taggedServiceIds = $container->findTaggedServiceIds('ezpublish.search.elasticsearch.content.facet_builder_visitor');
        foreach ($taggedServiceIds as $id => $attributes) {
            $aggregateFacetBuilderVisitorDefinition->addMethodCall(
                'addVisitor',
                [new Reference($id)]
            );
        }
    }
}
