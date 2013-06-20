<?php
/**
 * File containing the InputParser CompilerPass class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InputParserPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish_rest.input_parsing_dispatcher' ) )
        {
            return;
        }

        $definition = $container->getDefinition( 'input_parsing_dispatcher' );

        // @todo rethink the relationships between registries. Rename if required.
        foreach ( $container->findTaggedServiceIds( 'ezpublish_rest.input_parser' ) as $id => $attributes )
        {
            if ( !isset( $attributes[0]['alias'] ) )
                throw new \LogicException( 'ezpublish_rest.input_parser service tag needs an "alias" attribute to identify the field type. None given.' );

            $definition->addParser(
                'registerProcessor',
                array( $attributes[0]["alias"], new Reference( $id ) )
            );
        }

    }
}
