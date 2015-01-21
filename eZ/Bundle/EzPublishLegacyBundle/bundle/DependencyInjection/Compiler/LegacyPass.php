<?php
/**
 * File containing the LegacyPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LegacyPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish_legacy.templating.delegating_converter' ) )
            return;

        $definition = $container->getDefinition( 'ezpublish_legacy.templating.delegating_converter' );
        foreach ( $container->findTaggedServiceIds( 'ezpublish_legacy.templating.converter' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['for'] ) )
                    continue;

                $definition->addMethodCall(
                    'addConverter',
                    array(
                        new Reference( $id ),
                        $attribute['for']
                    )
                );
            }
        }
    }
}
