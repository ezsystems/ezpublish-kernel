<?php
/**
 * File containing the FragmentPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Tweaks Symfony fragment framework.
 */
class FragmentPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'fragment.listener' ) )
        {
            return;
        }

        $fragmentListenerDef = $container->findDefinition( 'fragment.listener' );
        $fragmentListenerDef
            ->setFactoryService( 'ezpublish.fragment_listener.factory' )
            ->setFactoryMethod( 'buildFragmentListener' )
            ->addArgument( '%fragment.listener.class%' );

        foreach ( array( 'esi', 'hinclude' ) as $rendererType )
        {
            if ( !$container->hasDefinition( "fragment.renderer.$rendererType" ) )
            {
                continue;
            }

            // SiteAccess injection must be in first position.
            $rendererDef = $container->findDefinition( "fragment.renderer.$rendererType" );
            $methodCalls = $rendererDef->getMethodCalls();
            array_unshift(
                $methodCalls,
                array(
                    'setSiteAccess',
                    array( new Reference( 'ezpublish.siteaccess' ) )
                )
            );
            $rendererDef->setMethodCalls( $methodCalls );
        }
    }
}
