<?php
/**
 * File containing the AddFieldTypePass class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register eZ Publish field types.
 */
class AddFieldTypePass implements CompilerPassInterface
{

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.api.repository.factory' ) )
            return;

        $repositoryFactoryDef = $container->getDefinition( 'ezpublish.api.repository.factory' );

        // Field types.
        // Alias attribute is the field type string.
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType' ) as $id => $attributes )
        {
            if ( !isset( $attributes[0]['alias'] ) )
                throw new \LogicException( 'ezpublish.fieldType service tag needs an "alias" attribute to identify the field type. None given.' );

            $repositoryFactoryDef->addMethodCall(
                'registerFieldType',
                array(
                    // Only pass the service Id since field types will be lazy loaded via the service container
                    $id,
                    $attributes[0]['alias']
                )
            );
        }

        // Gateways for external storage handlers.
        // Alias attribute is the corresponding field type string.
        $externalStorageGateways = array();
        // Referencing the services by alias (field type string)
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType.externalStorageHandler.gateway' ) as $id => $attributes )
        {
            if ( !isset( $attributes[0]['alias'] ) )
                throw new \LogicException( 'ezpublish.fieldType.externalStorageHandler.gateway service tag needs an "alias" attribute to identify the field type. None given.' );

            if ( !isset( $attributes[0]['identifier'] ) )
                throw new \LogicException( 'ezpublish.fieldType.externalStorageHandler.gateway service tag needs an "identifier" attribute to identify the gateway. None given.' );

            $externalStorageGateways[$attributes[0]['alias']] = array(
                'id'            => $id,
                'identifier'    => $attributes[0]['identifier']
            );
        }

        // External storage handlers for field types that need them.
        // Alias attribute is the field type string.
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType.externalStorageHandler' ) as $id => $attributes )
        {
            if ( !isset( $attributes[0]['alias'] ) )
                throw new \LogicException( 'ezpublish.fieldType.externalStorageHandler service tag needs an "alias" attribute to identify the field type. None given.' );

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
                if ( !isset( $externalStorageGateways[$attributes[0]['alias']] ) )
                    throw new \LogicException(
                        "External storage handler '$id' for field type {$attributes[0]['alias']} needs a storage gateway but none was given.
                        Consider defining a storage gateway as a service for this field type and add the 'ezpublish.fieldType.externalStorageHandler.gateway tag'"
                    );

                $storageHandlerDef->addMethodCall(
                    'addGateway',
                    array(
                        $externalStorageGateways[$attributes[0]['alias']]['identifier'],
                        new Reference( $externalStorageGateways[$attributes[0]['alias']]['id'] )
                    )
                );
            }

            $repositoryFactoryDef->addMethodCall(
                'registerExternalStorageHandler',
                array(
                    // Only pass the service Id since field types will be lazy loaded via the service container
                    $id,
                    $attributes[0]['alias']
                )
            );
        }
    }
}
