<?php
/**
 * File containing the AggregateCriterionVisitorPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Solr;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        if ( !$container->hasDefinition( 'ezpublish.persistence.solr.search.content.criterion_visitor.aggregate' ) )
        {
            return;
        }

        $aggregateCriterionVisitorDefinition = $container->getDefinition(
            'ezpublish.persistence.solr.search.content.criterion_visitor.aggregate'
        );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.solr.search.content.criterion_visitor' ) as $id => $attributes )
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
