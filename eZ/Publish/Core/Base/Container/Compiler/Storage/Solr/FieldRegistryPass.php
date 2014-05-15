<?php
/**
 * File containing the FieldRegistryPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Solr;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

/**
 * This compiler pass will register eZ Publish indexable field types.
 */
class FieldRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.solr.search.field_registry' ) )
        {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition( 'ezpublish.persistence.solr.search.field_registry' );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType.indexable' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                {
                    throw new LogicException(
                        'ezpublish.fieldType.indexable service tag needs an "alias" attribute to ' .
                        'identify the indexable field type. None given.'
                    );
                }

                $fieldRegistryDefinition->addMethodCall(
                    'registerType',
                    array(
                        $attribute['alias'],
                        new Reference( $id ),
                    )
                );
            }
        }
    }
}
