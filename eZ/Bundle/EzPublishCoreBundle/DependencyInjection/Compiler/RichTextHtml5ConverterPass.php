<?php
/**
 * File containing the RichTextHtml5ConverterPass class.
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
 * Compiler pass for the RichText Aggregate converter tags.
 * @see \eZ\Publish\Core\FieldType\RichText\Converter\Aggregate
 */
class RichTextHtml5ConverterPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.fieldType.ezrichtext.converter.output.xhtml5' ) )
        {
            return;
        }

        $html5ConverterDefinition = $container->getDefinition( 'ezpublish.fieldType.ezrichtext.converter.output.xhtml5' );
        $taggedServiceIds = $container->findTaggedServiceIds( 'ezpublish.ezrichtext.converter.output.xhtml5' );

        $convertersByPriority = array();
        foreach ( $taggedServiceIds as $id => $tags )
        {
            foreach ( $tags as $tag )
            {
                $priority = isset( $tag['priority'] ) ? (int)$tag['priority'] : 0;
                $convertersByPriority[$priority][] = new Reference( $id );
            }
        }

        if ( count( $convertersByPriority ) > 0 )
        {
            $html5ConverterDefinition->setArguments(
                array( $this->sortConverters( $convertersByPriority ) )
            );
        }
    }

    /**
     * Transforms a two-dimensional array of converters, indexed by priority,
     * into a flat array of Reference objects.
     *
     * @param array $convertersByPriority
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function sortConverters( array $convertersByPriority )
    {
        ksort( $convertersByPriority );

        return call_user_func_array( 'array_merge', $convertersByPriority );
    }
}
