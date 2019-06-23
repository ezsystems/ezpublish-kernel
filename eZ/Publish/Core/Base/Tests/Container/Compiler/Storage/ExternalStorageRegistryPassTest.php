<?php

/**
 * File containing the ExternalStorageRegistryPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage;

use eZ\Publish\Core\Base\Container\Compiler\Storage\ExternalStorageRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\GatewayBasedStorageHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExternalStorageRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.persistence.external_storage_registry.factory', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExternalStorageRegistryPass());
    }

    public function testRegisterExternalStorageHandler()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag('ezpublish.fieldType.externalStorageHandler', ['alias' => $fieldTypeIdentifier]);
        $serviceId = 'some_service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            [$serviceId, $fieldTypeIdentifier]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerNoAlias()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag('ezpublish.fieldType.externalStorageHandler');
        $serviceId = 'some_service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            [$serviceId, $fieldTypeIdentifier]
        );
    }

    public function testRegisterExternalStorageHandlerWithGateway()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag('ezpublish.fieldType.externalStorageHandler', ['alias' => $fieldTypeIdentifier]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag(
            'ezpublish.fieldType.externalStorageHandler.gateway',
            ['alias' => $fieldTypeIdentifier, 'identifier' => $gatewayIdentifier]
        );
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition($gatewayServiceId, $gatewayDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            [$storageHandlerServiceId, $fieldTypeIdentifier]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $storageHandlerServiceId,
            'addGateway',
            [$gatewayIdentifier, new Reference($gatewayServiceId)]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerWithoutRegisteredGateway()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag('ezpublish.fieldType.externalStorageHandler', ['alias' => $fieldTypeIdentifier]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            [$storageHandlerServiceId, $fieldTypeIdentifier]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerWithGatewayNoAlias()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag('ezpublish.fieldType.externalStorageHandler', ['alias' => $fieldTypeIdentifier]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag('ezpublish.fieldType.externalStorageHandler.gateway');
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition($gatewayServiceId, $gatewayDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            [$storageHandlerServiceId, $fieldTypeIdentifier]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerWithGatewayNoIdentifier()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag('ezpublish.fieldType.externalStorageHandler', ['alias' => $fieldTypeIdentifier]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag(
            'ezpublish.fieldType.externalStorageHandler.gateway',
            ['alias' => $fieldTypeIdentifier]
        );
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition($gatewayServiceId, $gatewayDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            [$storageHandlerServiceId, $fieldTypeIdentifier]
        );
    }
}
