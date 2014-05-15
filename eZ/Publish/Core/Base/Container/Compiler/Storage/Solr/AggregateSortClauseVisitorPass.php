<?php
/**
 * File containing the AggregateSortClauseVisitorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Solr;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Solr Storage sort clause visitors.
 */
class AggregateSortClauseVisitorPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.solr.search.content.sort_clause_visitor.aggregate' ) )
        {
            return;
        }

        $aggregateSortClauseVisitorDefinition = $container->getDefinition(
            'ezpublish.persistence.solr.search.content.sort_clause_visitor.aggregate'
        );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.solr.search.content.sort_clause_visitor' ) as $id => $attributes )
        {
            $aggregateSortClauseVisitorDefinition->addMethodCall(
                'addVisitor',
                array(
                    new Reference( $id ),
                )
            );
        }
    }
}
