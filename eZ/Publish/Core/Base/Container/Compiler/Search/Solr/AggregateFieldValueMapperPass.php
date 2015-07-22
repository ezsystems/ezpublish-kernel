<?php

/**
 * File containing the AggregateFieldValueMapperPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Search\Solr;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Solr Storage field value mappers.
 */
class AggregateFieldValueMapperPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.search.solr.content.field_value_mapper.aggregate')) {
            return;
        }

        $aggregateFieldValueMapperDefinition = $container->getDefinition(
            'ezpublish.search.solr.content.field_value_mapper.aggregate'
        );

        foreach ($container->findTaggedServiceIds('ezpublish.search.solr.content.field_value_mapper') as $id => $attributes) {
            $aggregateFieldValueMapperDefinition->addMethodCall(
                'addMapper',
                array(
                    new Reference($id),
                )
            );
        }
    }
}
