<?php
/**
 * File containing the OutputVisitorPass class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for the ezpublish_rest.output.visitor tag.
 *
 * Maps an output visitor (json, xml...) to an accept-header
 * @todo The tag is much more limited in scope than the name shows. Refactor. More ways to map ?
 */
class OutputVisitorPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish_rest.output.visitor.dispatcher' ) )
        {
            return;
        }

        $definition = $container->getDefinition( 'ezpublish_rest.output.visitor.dispatcher' );

        foreach ( $container->findTaggedServiceIds( 'ezpublish_rest.output.visitor.dispatcher' ) as $id => $attributes )
        {
            if ( !isset( $attributes[0]['regexps'] ) || !is_array( $attributes[0]['regexps'] ) )
                throw new \LogicException( 'ezpublish_rest.output.visitor service tag needs a "regexps" array attribute to identify the field type. None given.' );

            foreach( $attributes[0]['regexps'] as $regexp )
            {
                $definition->addMethodCall(
                    'addVisitor',
                    array( $regexp, new Reference( $id ) )
                );
            }
        }

    }
}
