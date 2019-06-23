<?php

/**
 * File containing the CriterionVisitorDispatcherContentPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search\Elasticsearch;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Elasticsearch Search Engine criterion visitors for Content Search.
 */
class CriterionVisitorDispatcherContentPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.search.elasticsearch.content.criterion_visitor_dispatcher')) {
            return;
        }

        $aggregateCriterionVisitorDefinition = $container->getDefinition(
            'ezpublish.search.elasticsearch.content.criterion_visitor_dispatcher'
        );

        foreach ($container->findTaggedServiceIds('ezpublish.search.elasticsearch.content.criterion_visitor') as $id => $attributes) {
            $aggregateCriterionVisitorDefinition->addMethodCall(
                'addVisitor',
                [
                    new Reference($id),
                ]
            );
        }
    }
}
