<?php
/**
 * File containing the IdentityDefinerPas class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class IdentityDefinerPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.user.hash_generator' ) )
            return;

        $hashGeneratorDef = $container->getDefinition( 'ezpublish.user.hash_generator' );
        foreach ( $container->findTaggedServiceIds( 'ezpublish.identity_definer' ) as $id => $attributes )
        {
            $hashGeneratorDef->addMethodCall(
                'setIdentityDefiner',
                array( new Reference( $id ) )
            );
        }
    }
}
