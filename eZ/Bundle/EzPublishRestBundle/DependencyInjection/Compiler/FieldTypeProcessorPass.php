<?php
/**
 * File containing the FieldTypeProcessorPass class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldTypeProcessorPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish_rest.field_type_processor_registry' ) )
        {
            return;
        }

        $definition = $container->getDefinition( 'ezpublish_rest.field_type_processor_registry' );

        foreach ( $container->findTaggedServiceIds( 'ezpublish_rest.field_type_processor' ) as $id => $attributes )
        {
            if ( !isset( $attributes[0]['alias'] ) )
                throw new \LogicException( 'ezpublish_rest.field_type_processor service tag needs an "alias" attribute to identify the field type. None given.' );

            $definition->addMethodCall(
                'registerProcessor',
                array( $attributes[0]["alias"], new Reference( $id ) )
            );
        }

    }
}
