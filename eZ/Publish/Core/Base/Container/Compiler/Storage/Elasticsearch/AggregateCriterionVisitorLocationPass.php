<?php
/**
 * File containing the AggregateCriterionVisitorLocationPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Elasticsearch;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Elasticsearch Storage criterion visitors.
 */
class AggregateCriterionVisitorLocationPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.elasticsearch.search.location.criterion_visitor.aggregate' ) )
        {
            return;
        }

        $aggregateCriterionVisitorDefinition = $container->getDefinition(
            'ezpublish.persistence.elasticsearch.search.location.criterion_visitor.aggregate'
        );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.elasticsearch.search.location.criterion_visitor' ) as $id => $attributes )
        {
            $aggregateCriterionVisitorDefinition->addMethodCall(
                'addVisitor',
                array(
                    new Reference( $id ),
                )
            );
        }
    }
}
