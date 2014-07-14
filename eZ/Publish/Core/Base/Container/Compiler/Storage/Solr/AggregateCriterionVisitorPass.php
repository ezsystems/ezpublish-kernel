<?php
/**
 * File containing the AggregateCriterionVisitorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Solr;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will register Solr Storage criterion visitors.
 */
class AggregateCriterionVisitorPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if (
            !$container->hasDefinition( 'ezpublish.persistence.solr.search.content.criterion_visitor.aggregate' ) &&
            !$container->hasDefinition( 'ezpublish.persistence.solr.search.location.criterion_visitor.aggregate' )
        )
        {
            return;
        }

        if ( $container->hasDefinition( 'ezpublish.persistence.solr.search.content.criterion_visitor.aggregate' ) )
        {
            $aggregateContentCriterionVisitorDefinition = $container->getDefinition(
                'ezpublish.persistence.solr.search.content.criterion_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ezpublish.persistence.solr.search.content.criterion_visitor'
            );

            $this->addHandlers( $aggregateContentCriterionVisitorDefinition, $visitors );
        }

        if ( $container->hasDefinition( 'ezpublish.persistence.solr.search.location.criterion_visitor.aggregate' ) )
        {
            $aggregateLocationCriterionVisitorDefinition = $container->getDefinition(
                'ezpublish.persistence.solr.search.location.criterion_visitor.aggregate'
            );

            $visitors = $container->findTaggedServiceIds(
                'ezpublish.persistence.solr.search.location.criterion_visitor'
            );

            $this->addHandlers( $aggregateLocationCriterionVisitorDefinition, $visitors );
        }
    }

    protected function addHandlers( Definition $definition, $handlers )
    {
        foreach ( $handlers as $id => $attributes )
        {
            $definition->addMethodCall( 'addVisitor', array( new Reference( $id ) ) );
        }
    }
}
