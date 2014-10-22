<?php
/**
 * File containing the AggregateSortClauseVisitorLocationPass class.
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
 * This compiler pass will register Elasticsearch Storage sort clause visitors.
 */
class AggregateSortClauseVisitorLocationPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.elasticsearch.search.location.sort_clause_visitor.aggregate' ) )
        {
            return;
        }

        $aggregateSortClauseVisitorDefinition = $container->getDefinition(
            'ezpublish.persistence.elasticsearch.search.location.sort_clause_visitor.aggregate'
        );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.elasticsearch.search.location.sort_clause_visitor' ) as $id => $attributes )
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
