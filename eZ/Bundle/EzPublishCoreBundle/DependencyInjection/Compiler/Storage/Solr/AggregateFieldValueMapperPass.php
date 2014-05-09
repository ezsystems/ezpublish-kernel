<?php
/**
 * File containing the AggregateFieldValueMapperPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\Storage\Solr;

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
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.solr.search.content.field_value_mapper.aggregate' ) )
        {
            return;
        }

        $aggregateFieldValueMapperDefinition = $container->getDefinition(
            'ezpublish.persistence.solr.search.content.field_value_mapper.aggregate'
        );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.solr.search.content.field_value_mapper' ) as $id => $attributes )
        {
            $aggregateFieldValueMapperDefinition->addMethodCall(
                'addMapper',
                array(
                    new Reference( $id ),
                )
            );
        }
    }
}
