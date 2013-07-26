<?php
/**
 * File containing the ViewPass class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The ViewPass adds DIC compiler pass related to content view.
 * This includes adding ContentViewProvider implementations.
 *
 * @see \eZ\Publish\Core\MVC\Symfony\View\Manager
 * @see \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider
 */
abstract class ViewPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( "ezpublish.view_manager" ) )
            return;

        $viewManagerDef = $container->getDefinition( "ezpublish.view_manager" );
        foreach ( $container->findTaggedServiceIds( static::VIEW_PROVIDER_IDENTIFIER ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                $priority = isset( $attribute["priority"] ) ? (int)$attribute["priority"] : 0;
                // Priority range is between -255 (the lowest) and 255 (the highest)
                $priority = max( min( $priority, 255 ), -255 );

                $viewManagerDef->addMethodCall(
                    static::ADD_VIEW_PROVIDER_METHOD,
                    array(
                        new Reference( $id ),
                        $priority
                    )
                );
            }
        }
    }
}
