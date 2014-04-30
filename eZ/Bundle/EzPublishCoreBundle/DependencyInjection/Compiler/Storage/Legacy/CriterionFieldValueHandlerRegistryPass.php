<?php
/**
 * File containing the CriterionFieldValueHandlerRegistryPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\Storage\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Legacy Storage criterion field value handlers.
 */
class CriterionFieldValueHandlerRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler.registry' ) )
            return;

        $registry = $container->getDefinition( 'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler.registry' );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                {
                    throw new LogicException(
                        'ezpublish.storageEngine.legacy.converter service tag needs an "alias" attribute to identify the field type. None given.'
                    );
                }

                $registry->addMethodCall(
                    'register',
                    array(
                        $attribute['alias'],
                        new Reference( $id )
                    )
                );
            }
        }
    }
}
