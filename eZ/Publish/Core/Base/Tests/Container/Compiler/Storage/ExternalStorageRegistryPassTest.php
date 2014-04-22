<?php
/**
 * File containing the ExternalStorageRegistryPassTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage;

use eZ\Publish\Core\Base\Container\Compiler\Storage\ExternalStorageRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExternalStorageRegistryPassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.persistence.external_storage_registry.factory', new Definition() );
    }

    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new ExternalStorageRegistryPass() );
    }

    public function testRegisterExternalStorageHandler()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag( 'ezpublish.fieldType.externalStorageHandler', array( 'alias' => $fieldTypeIdentifier ) );
        $serviceId = 'some_service_id';
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            array( $serviceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerNoAlias()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag( 'ezpublish.fieldType.externalStorageHandler' );
        $serviceId = 'some_service_id';
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            array( $serviceId, $fieldTypeIdentifier )
        );
    }

    public function testRegisterExternalStorageHandlerWithGateway()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\GatewayBasedStorageHandler'
        );
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag( 'ezpublish.fieldType.externalStorageHandler', array( 'alias' => $fieldTypeIdentifier ) );
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition( $storageHandlerServiceId, $handlerDef );

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag(
            'ezpublish.fieldType.externalStorageHandler.gateway',
            array( 'alias' => $fieldTypeIdentifier, 'identifier' => $gatewayIdentifier )
        );
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition( $gatewayServiceId, $gatewayDef );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            array( $storageHandlerServiceId, $fieldTypeIdentifier )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $storageHandlerServiceId,
            'addGateway',
            array( $gatewayIdentifier, new Reference( $gatewayServiceId ) )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerWithoutRegisteredGateway()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\GatewayBasedStorageHandler'
        );
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag( 'ezpublish.fieldType.externalStorageHandler', array( 'alias' => $fieldTypeIdentifier ) );
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition( $storageHandlerServiceId, $handlerDef );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            array( $storageHandlerServiceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerWithGatewayNoAlias()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\GatewayBasedStorageHandler'
        );
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag( 'ezpublish.fieldType.externalStorageHandler', array( 'alias' => $fieldTypeIdentifier ) );
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition( $storageHandlerServiceId, $handlerDef );

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag( 'ezpublish.fieldType.externalStorageHandler.gateway' );
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition( $gatewayServiceId, $gatewayDef );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            array( $storageHandlerServiceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStorageHandlerWithGatewayNoIdentifier()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\GatewayBasedStorageHandler'
        );
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag( 'ezpublish.fieldType.externalStorageHandler', array( 'alias' => $fieldTypeIdentifier ) );
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition( $storageHandlerServiceId, $handlerDef );

        $gatewayDef = new Definition();
        $gatewayIdentifier = 'LegacyStorage';
        $gatewayDef->addTag(
            'ezpublish.fieldType.externalStorageHandler.gateway',
            array( 'alias' => $fieldTypeIdentifier )
        );
        $gatewayServiceId = 'gateway_service';
        $this->setDefinition( $gatewayServiceId, $gatewayDef );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_registry.factory',
            'registerExternalStorageHandler',
            array( $storageHandlerServiceId, $fieldTypeIdentifier )
        );
    }
}
