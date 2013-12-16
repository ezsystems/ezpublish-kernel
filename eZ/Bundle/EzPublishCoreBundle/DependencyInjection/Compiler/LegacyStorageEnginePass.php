<?php
/**
 * File containing the LegacyStorageEnginePass class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register eZ Publish field types.
 */
class LegacyStorageEnginePass implements CompilerPassInterface
{

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.api.storage_engine.legacy.factory' ) )
            return;

        $legacyStorageEngineDef = $container->getDefinition( 'ezpublish.api.storage_engine.legacy.factory' );

        // Field types.
        // Alias attribute is the field type string.
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new \LogicException( 'ezpublish.fieldType service tag needs an "alias" attribute to identify the field type. None given.' );

                $legacyStorageEngineDef->addMethodCall(
                    'registerFieldType',
                    array(
                        // Only pass the service Id since field types will be lazy loaded via the service container
                        $id,
                        $attribute['alias']
                    )
                );
            }
        }

        foreach ( $container->findTaggedServiceIds( 'ezpublish.storageEngine.legacy.converter' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new LogicException( 'ezpublish.storageEngine.legacy.converter service tag needs an "alias" attribute to identify the field type. None given.' );

                if ( isset( $attribute['lazy'] ) && $attribute['lazy'] === true )
                {
                    if ( !isset( $attribute['callback'] ) )
                        throw new LogicException( "Converter service '$id' is marked as lazy but no callback is provided! Please provide a callback." );

                    $converter = $attribute['callback'];
                    if ( strpos( $converter, '::' ) === 0 )
                    {
                        $converter = $container->getDefinition( $id )->getClass() . $converter;
                    }
                }
                else
                {
                    $converter = new Reference( $id );
                }

                $legacyStorageEngineDef->addMethodCall(
                    'registerFieldTypeConverter',
                    array(
                        $attribute['alias'],
                        $converter
                    )
                );
            }
        }
    }
}
