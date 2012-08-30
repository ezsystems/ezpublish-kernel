<?php
/**
 * File containing the TwigTweaksPass class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Reference,
    ReflectionClass;

/**
 * This compiler pass will register eZ Publish field types.
 */
class TwigTweaksPass implements CompilerPassInterface
{

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'twig.loader.chain' ) )
            return;

        // Add registered loaders to the chain loader
        $refChainLoader = $container->getDefinition( 'twig.loader.chain' );
        foreach ( $container->findTaggedServiceIds( 'twig.loader' ) as $id => $attributes )
        {
            $refChainLoader->addMethodCall( 'addLoader', array( new Reference( $id ) ) );
        }

        // Adding base templates directory in the twig environment
        $reflContentExtensionClass = new ReflectionClass( 'eZ\\Publish\\MVC\\Templating\\Twig\\Extension\\ContentExtension' );
        $tplDir = dirname( $reflContentExtensionClass->getFileName() ) . '/../../../Resources/views';
        $container->getDefinition( 'twig.loader.filesystem' )->addMethodCall(
            'addPath',
            array(
                 "$tplDir/Content"
            )
        );
    }
}
