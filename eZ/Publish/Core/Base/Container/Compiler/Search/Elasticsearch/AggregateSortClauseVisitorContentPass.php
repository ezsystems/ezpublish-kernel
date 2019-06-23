<?php

/**
 * File containing the AggregateSortClauseVisitorContentPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Elasticsearch Search Engine sort clause visitors.
 */
class AggregateSortClauseVisitorContentPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.search.elasticsearch.content.sort_clause_visitor.aggregate')) {
            return;
        }

        $aggregateSortClauseVisitorDefinition = $container->getDefinition(
            'ezpublish.search.elasticsearch.content.sort_clause_visitor.aggregate'
        );

        foreach ($container->findTaggedServiceIds('ezpublish.search.elasticsearch.content.sort_clause_visitor') as $id => $attributes) {
            $aggregateSortClauseVisitorDefinition->addMethodCall(
                'addVisitor',
                [
                    new Reference($id),
                ]
            );
        }
    }
}
