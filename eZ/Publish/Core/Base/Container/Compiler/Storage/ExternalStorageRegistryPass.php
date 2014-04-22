<?php
/**
 * File containing the ExternalStorageRegistryPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

/**
 * This compiler pass will register eZ Publish external storage handlers and gateways.
 */
class ExternalStorageRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.external_storage_registry.factory' ) )
        {
            return;
        }

        $externalStorageRegistryFactoryDefinition = $container->getDefinition(
            'ezpublish.persistence.external_storage_registry.factory'
        );

        // Gateways for external storage handlers.
        // Alias attribute is the corresponding field type string.
        $externalStorageGateways = array();
        // Referencing the services by alias (field type string)
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType.externalStorageHandler.gateway' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new LogicException( 'ezpublish.fieldType.externalStorageHandler.gateway service tag needs an "alias" attribute to identify the field type. None given.' );

                if ( !isset( $attribute['identifier'] ) )
                    throw new LogicException( 'ezpublish.fieldType.externalStorageHandler.gateway service tag needs an "identifier" attribute to identify the gateway. None given.' );

                $externalStorageGateways[$attribute['alias']] = array(
                    'id' => $id,
                    'identifier' => $attribute['identifier']
                );
            }
        }

        // External storage handlers for field types that need them.
        // Alias attribute is the field type string.
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType.externalStorageHandler' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new LogicException( 'ezpublish.fieldType.externalStorageHandler service tag needs an "alias" attribute to identify the field type. None given.' );

                // If the storage handler is gateway based, then we need to add a corresponding gateway to it.
                // Will throw a LogicException if no gateway is defined for this field type.
                $storageHandlerDef = $container->findDefinition( $id );
                $storageHandlerClass = $storageHandlerDef->getClass();
                if ( preg_match( '/^%([^%\s]+)%$/', $storageHandlerClass, $match ) )
                    $storageHandlerClass = $container->getParameter( $match[1] );

                if (
                    is_subclass_of(
                        $storageHandlerClass,
                        'eZ\\Publish\\Core\\FieldType\\GatewayBasedStorage'
                    )
                )
                {
                    if ( !isset( $externalStorageGateways[$attribute['alias']] ) )
                        throw new LogicException(
                            "External storage handler '$id' for field type {$attribute['alias']} needs a storage gateway but none was given.
                        Consider defining a storage gateway as a service for this field type and add the 'ezpublish.fieldType.externalStorageHandler.gateway tag'"
                        );

                    $storageHandlerDef->addMethodCall(
                        'addGateway',
                        array(
                            $externalStorageGateways[$attribute['alias']]['identifier'],
                            new Reference( $externalStorageGateways[$attribute['alias']]['id'] )
                        )
                    );
                }

                $externalStorageRegistryFactoryDefinition->addMethodCall(
                    'registerExternalStorageHandler',
                    array(
                        $id,
                        $attribute['alias'],
                    )
                );
            }
        }
    }
}
