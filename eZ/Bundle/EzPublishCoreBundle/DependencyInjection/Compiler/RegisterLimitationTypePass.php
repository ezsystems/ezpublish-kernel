<?php
/**
 * File containing the RegisterLimitationTypePass class.
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
 * This compiler pass will register eZ Publish field types.
 */
class RegisterLimitationTypePass implements CompilerPassInterface
{

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.api.repository.factory' ) )
            return;

        $repositoryFactoryDef = $container->getDefinition( 'ezpublish.api.repository.factory' );

        // Limitation types.
        // Alias attribute is the limitation type name.
        foreach ( $container->findTaggedServiceIds( 'ezpublish.limitationType' ) as $id => $attributes )
        {
            if ( !isset( $attributes[0]['alias'] ) )
                throw new \LogicException( 'ezpublish.limitationType service tag needs an "alias" attribute to identify the limitation type. None given.' );

            $repositoryFactoryDef->addMethodCall(
                'registerLimitationType',
                array(
                    $attributes[0]['alias'],
                    new Reference( $id )
                )
            );
        }
    }
}
