<?php
/**
 * File containing the RegisterLimitationTypePass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register limitation types.
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
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new \LogicException( 'ezpublish.limitationType service tag needs an "alias" attribute to identify the limitation type. None given.' );

                $repositoryFactoryDef->addMethodCall(
                    'registerLimitationType',
                    array(
                        $attribute['alias'],
                        new Reference( $id )
                    )
                );
            }
        }
    }
}
