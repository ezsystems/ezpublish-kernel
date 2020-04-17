<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
    public const EXTERNAL_STORAGE_HANDLER_SERVICE_TAG = 'ezplatform.field_type.external_storage_handler';
    public const EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG = 'ezplatform.field_type.external_storage_handler.gateway';

    public const DEPRECATED_EXTERNAL_STORAGE_HANDLER_SERVICE_TAG = 'ezpublish.fieldType.externalStorageHandler';
    public const DEPRECATED_EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG = 'ezpublish.fieldType.externalStorageHandler.gateway';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.persistence.external_storage_registry')) {
            return;
        }

        $externalStorageRegistryDefinition = $container->getDefinition(
            'ezpublish.persistence.external_storage_registry'
        );

        // Gateways for external storage handlers.
        // Alias attribute is the corresponding field type string.
        $externalStorageGateways = [];

        $deprecatedExternalStorageHandlerGatewayTags = $container->findTaggedServiceIds(
            self::DEPRECATED_EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG
        );

        foreach ($deprecatedExternalStorageHandlerGatewayTags as $deprecatedExternalStorageHandlerGatewayTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s` instead.',
                    self::DEPRECATED_EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG,
                    self::EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG
                ),
                E_USER_DEPRECATED
            );
        }

        $externalStorageHandlerGatewayTags = array_merge(
            $deprecatedExternalStorageHandlerGatewayTags,
            $container->findTaggedServiceIds(
                self::EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG
            )
        );

        // Referencing the services by alias (field type string)
        foreach ($externalStorageHandlerGatewayTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            'The %s or %s service tag needs an "alias" attribute to identify the Field Type.',
                            self::DEPRECATED_EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG,
                            self::EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG
                        )
                    );
                }

                if (!isset($attribute['identifier'])) {
                    throw new LogicException(
                        sprintf(
                            'The %s or %s service tag needs an "identifier" attribute to identify the gateway.',
                            self::DEPRECATED_EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG,
                            self::EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG
                        )
                    );
                }

                $externalStorageGateways[$attribute['alias']] = [
                    'id' => $id,
                    'identifier' => $attribute['identifier'],
                ];
            }
        }

        $deprecatedExternalStorageHandlerTags = $container->findTaggedServiceIds(
            self::DEPRECATED_EXTERNAL_STORAGE_HANDLER_SERVICE_TAG
        );

        foreach ($deprecatedExternalStorageHandlerTags as $deprecatedExternalStorageHandlerTag) {
            @trigger_error(
                sprintf(
                    '`%s` service tag is deprecated and will be removed in eZ Platform 4.0. Please use `%s` instead.',
                    self::DEPRECATED_EXTERNAL_STORAGE_HANDLER_SERVICE_TAG,
                    self::EXTERNAL_STORAGE_HANDLER_SERVICE_TAG
                ),
                E_USER_DEPRECATED
            );
        }

        $externalStorageHandlerTags = array_merge(
            $deprecatedExternalStorageHandlerTags,
            $container->findTaggedServiceIds(
                self::EXTERNAL_STORAGE_HANDLER_SERVICE_TAG
            )
        );

        // External storage handlers for field types that need them.
        // Alias attribute is the field type string.
        foreach ($externalStorageHandlerTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            'The %s or %s service tag needs an "alias" attribute to identify the Field Type.',
                            self::DEPRECATED_EXTERNAL_STORAGE_HANDLER_SERVICE_TAG,
                            self::EXTERNAL_STORAGE_HANDLER_SERVICE_TAG
                        )
                    );
                }

                // If the storage handler is gateway based, then we need to add a corresponding gateway to it.
                // Will throw a LogicException if no gateway is defined for this field type.
                $storageHandlerDef = $container->findDefinition($id);
                $storageHandlerClass = $storageHandlerDef->getClass();
                if (preg_match('/^%([^%\s]+)%$/', $storageHandlerClass, $match)) {
                    $storageHandlerClass = $container->getParameter($match[1]);
                }

                if (
                    is_subclass_of(
                        $storageHandlerClass,
                        'eZ\\Publish\\Core\\FieldType\\GatewayBasedStorage'
                    )
                ) {
                    if (!isset($externalStorageGateways[$attribute['alias']])) {
                        throw new LogicException(
                            "External storage handler '$id' for Field Type {$attribute['alias']} needs a storage gateway.
                            Consider defining a storage gateway as a service for this Field Type and add the 'ezplatform.field_type.external_storage_handler.gateway tag'"
                        );
                    }

                    $storageHandlerDef->addMethodCall(
                        'addGateway',
                        [
                            $externalStorageGateways[$attribute['alias']]['identifier'],
                            new Reference($externalStorageGateways[$attribute['alias']]['id']),
                        ]
                    );
                }

                $externalStorageRegistryDefinition->addMethodCall(
                    'register',
                    [
                        $attribute['alias'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
