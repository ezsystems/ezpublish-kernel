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
    protected function setUp(): void
    {
        parent::setUp();
        $this->setDefinition('ezpublish.persistence.external_storage_registry', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ExternalStorageRegistryPass());
    }

    /**
     * @dataProvider externalStorageHandlerTagsProvider
     */
    public function testRegisterExternalStorageHandler(string $tag)
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag($tag, ['alias' => $fieldTypeIdentifier]);
        $serviceId = 'some_service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry',
            'register',
            [$fieldTypeIdentifier, new Reference($serviceId)]
        );
    }

    /**
     * @dataProvider externalStorageHandlerTagsProvider
     */
    public function testRegisterExternalStorageHandlerNoAlias(string $tag)
    {
        $this->expectException(\LogicException::class);

        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag($tag);
        $serviceId = 'some_service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry',
            'register',
            [$fieldTypeIdentifier, new Reference($serviceId)]
        );
    }

    /**
     * @dataProvider externalStorageHandlerGatewayTagsProvider
     */
    public function testRegisterExternalStorageHandlerWithGateway(string $tag)
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag(ExternalStorageRegistryPass::EXTERNAL_STORAGE_HANDLER_SERVICE_TAG, [
            'alias' => $fieldTypeIdentifier,
        ]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag($tag, [
            'alias' => $fieldTypeIdentifier,
            'identifier' => $gatewayIdentifier,
        ]);
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition($gatewayServiceId, $gatewayDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry',
            'register',
            [$fieldTypeIdentifier, new Reference($storageHandlerServiceId)]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $storageHandlerServiceId,
            'addGateway',
            [$gatewayIdentifier, new Reference($gatewayServiceId)]
        );
    }

    /**
     * @dataProvider externalStorageHandlerGatewayTagsProvider
     */
    public function testRegisterExternalStorageHandlerWithoutRegisteredGateway(string $tag)
    {
        $this->expectException(\LogicException::class);

        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag($tag, [
            'alias' => $fieldTypeIdentifier,
        ]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry',
            'register',
            [$fieldTypeIdentifier, new Reference($storageHandlerServiceId)]
        );
    }

    /**
     * @dataProvider externalStorageHandlerGatewayTagsProvider
     */
    public function testRegisterExternalStorageHandlerWithGatewayNoAlias(string $tag)
    {
        $this->expectException(\LogicException::class);

        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag(ExternalStorageRegistryPass::EXTERNAL_STORAGE_HANDLER_SERVICE_TAG, [
            'alias' => $fieldTypeIdentifier,
        ]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag($tag);
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition($gatewayServiceId, $gatewayDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry',
            'register',
            [$fieldTypeIdentifier, new Reference($storageHandlerServiceId)]
        );
    }

    /**
     * @dataProvider externalStorageHandlerGatewayTagsProvider
     */
    public function testRegisterExternalStorageHandlerWithGatewayNoIdentifier(string $tag)
    {
        $this->expectException(\LogicException::class);

        $handlerDef = new Definition();
        $handlerDef->setClass(GatewayBasedStorageHandler::class);
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag(ExternalStorageRegistryPass::EXTERNAL_STORAGE_HANDLER_SERVICE_TAG, [
            'alias' => $fieldTypeIdentifier,
        ]);
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition($storageHandlerServiceId, $handlerDef);

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag($tag, ['alias' => $fieldTypeIdentifier]);
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition($gatewayServiceId, $gatewayDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry',
            'register',
            [$fieldTypeIdentifier, new Reference($storageHandlerServiceId)]
        );
    }

    public function externalStorageHandlerTagsProvider(): array
    {
        return [
            [ExternalStorageRegistryPass::DEPRECATED_EXTERNAL_STORAGE_HANDLER_SERVICE_TAG],
            [ExternalStorageRegistryPass::EXTERNAL_STORAGE_HANDLER_SERVICE_TAG],
        ];
    }

    public function externalStorageHandlerGatewayTagsProvider(): array
    {
        return [
            [ExternalStorageRegistryPass::DEPRECATED_EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG],
            [ExternalStorageRegistryPass::EXTERNAL_STORAGE_HANDLER_GATEWAY_SERVICE_TAG],
        ];
    }
}
