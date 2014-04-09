<?php
/**
 * File containing the LegacyStorageEnginePass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will register eZ Publish field types.
 */
class CriteriaConverterPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if (
            !$container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.criteria_converter.content' ) &&
            !$container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.criteria_converter.location' )
        )
        {
            return;
        }

        $commonHandlers = $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.search.gateway.criterion_handler.common' );

        if ( $container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.criteria_converter.content' ) )
        {
            $criteriaConverterContent = $container->getDefinition( 'ezpublish.persistence.legacy.search.gateway.criteria_converter.content' );

            $contentHandlers = $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.search.gateway.criterion_handler.content' );

            $this->addHandlers( $criteriaConverterContent, $contentHandlers );
            $this->addHandlers( $criteriaConverterContent, $commonHandlers );
        }

        if ( $container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.criteria_converter.location' ) )
        {
            $criteriaConverterLocation = $container->getDefinition( 'ezpublish.persistence.legacy.search.gateway.criteria_converter.location' );

            $locationHandlers = $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.search.gateway.criterion_handler.location' );

            $this->addHandlers( $criteriaConverterLocation, $locationHandlers );
            $this->addHandlers( $criteriaConverterLocation, $commonHandlers );
        }
    }

    protected function addHandlers( Definition $definition, $handlers )
    {
        foreach ( $handlers as $id => $attributes )
        {
            $definition->addMethodCall( 'addHandler', array( new Reference( $id ) ) );
        }
    }
}
