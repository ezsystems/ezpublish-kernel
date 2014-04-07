<?php
/**
 * File containing the FieldTypeRegistryPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorage;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register eZ Publish field types.
 */
class FieldTypeRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.field_type_registry' ) )
        {
            return;
        }

        $repositoryFactoryDef = $container->getDefinition( 'ezpublish.persistence.field_type_registry' );

        // Field types.
        // Alias attribute is the field type string.
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                {
                    throw new \LogicException( 'ezpublish.fieldType service tag needs an "alias" attribute to identify the field type. None given.' );
                }

                $repositoryFactoryDef->addMethodCall(
                    'register',
                    array(
                        $attribute['alias'],
                        new Reference( $id ),
                    )
                );
            }
        }
    }
}
