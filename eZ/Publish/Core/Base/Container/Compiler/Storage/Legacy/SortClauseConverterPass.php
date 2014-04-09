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
class SortClauseConverterPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if (
            !$container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content' ) &&
            !$container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location' )
        )
        {
            return;
        }

        $commonHandlers = $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.common' );

        if ( $container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content' ) )
        {
            $sortClauseConverterContent = $container->getDefinition( 'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.content' );

            $contentHandlers = $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.content' );

            $this->addHandlers( $sortClauseConverterContent, $contentHandlers );
            $this->addHandlers( $sortClauseConverterContent, $commonHandlers );
        }

        if ( $container->hasDefinition( 'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location' ) )
        {
            $sortClauseConverterLocation = $container->getDefinition( 'ezpublish.persistence.legacy.search.gateway.sort_clause_converter.location' );

            $locationHandlers = $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.search.gateway.sort_clause_handler.location' );

            $this->addHandlers( $sortClauseConverterLocation, $locationHandlers );
            $this->addHandlers( $sortClauseConverterLocation, $commonHandlers );
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
