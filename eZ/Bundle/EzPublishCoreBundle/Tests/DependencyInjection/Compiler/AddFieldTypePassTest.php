<?php
/**
 * File containing the AddFieldTypePassTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTest;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddFieldTypePassTest extends AbstractCompilerPassTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.api.repository.factory', new Definition() );
        $this->setDefinition( 'ezpublish.fieldType.parameterProviderRegistry', new Definition() );
    }

    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new AddFieldTypePass() );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     */
    public function testAddSimpleFieldType()
    {
        $fieldTypeDef = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $fieldTypeDef->addTag( 'ezpublish.fieldType', array( 'alias' => $fieldTypeIdentifier ) );
        $fieldTypeServiceId = 'field_type_service';
        $this->setDefinition( $fieldTypeServiceId, $fieldTypeDef );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.repository.factory',
            'registerFieldType',
            array( $fieldTypeServiceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     *
     * @expectedException \LogicException
     */
    public function testAddSimpleFieldTypeNoAlias()
    {
        $fieldTypeDef = new Definition();
        $fieldTypeDef->addTag( 'ezpublish.fieldType' );
        $fieldTypeServiceId = 'field_type_service';
        $this->setDefinition( $fieldTypeServiceId, $fieldTypeDef );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.repository.factory',
            'registerFieldType',
            array( $fieldTypeServiceId, 'field_type_identifier' )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     */
    public function testAddParameterProvider()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag( 'ezpublish.fieldType.parameterProvider', array( 'alias' => $fieldTypeIdentifier ) );
        $serviceId = 'field_type_service';
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.parameterProviderRegistry',
            'setParameterProvider',
            array( $serviceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     *
     * @expectedException \LogicException
     */
    public function testAddParameterProviderNoAlias()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag( 'ezpublish.fieldType.parameterProvider' );
        $serviceId = 'some_service_id';
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.parameterProviderRegistry',
            'setParameterProvider',
            array( $serviceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     */
    public function testAddExternalStorageHandler()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag( 'ezpublish.fieldType.externalStorageHandler', array( 'alias' => $fieldTypeIdentifier ) );
        $serviceId = 'some_service_id';
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.repository.factory',
            'registerExternalStorageHandler',
            array( $serviceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     *
     * @expectedException \LogicException
     */
    public function testAddExternalStorageHandlerNoAlias()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'field_type_identifier';
        $def->addTag( 'ezpublish.fieldType.externalStorageHandler' );
        $serviceId = 'some_service_id';
        $this->setDefinition( $serviceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.repository.factory',
            'registerExternalStorageHandler',
            array( $serviceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     */
    public function testAddExternalStorageHandlerWithGateway()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler\Stubs\GatewayBasedStorageHandler'
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
            'ezpublish.api.repository.factory',
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
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     *
     * @expectedException \LogicException
     */
    public function testAddExternalStorageHandlerWithoutRegisteredGateway()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler\Stubs\GatewayBasedStorageHandler'
        );
        $fieldTypeIdentifier = 'field_type_identifier';
        $handlerDef->addTag( 'ezpublish.fieldType.externalStorageHandler', array( 'alias' => $fieldTypeIdentifier ) );
        $storageHandlerServiceId = 'external_storage_handler_id';
        $this->setDefinition( $storageHandlerServiceId, $handlerDef );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.repository.factory',
            'registerExternalStorageHandler',
            array( $storageHandlerServiceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     *
     * @expectedException \LogicException
     */
    public function testAddExternalStorageHandlerWithGatewayNoAlias()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler\Stubs\GatewayBasedStorageHandler'
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
            'ezpublish.api.repository.factory',
            'registerExternalStorageHandler',
            array( $storageHandlerServiceId, $fieldTypeIdentifier )
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\AddFieldTypePass::process
     *
     * @expectedException \LogicException
     */
    public function testAddExternalStorageHandlerWithGatewayNoIdentifier()
    {
        $handlerDef = new Definition();
        $handlerDef->setClass(
            'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler\Stubs\GatewayBasedStorageHandler'
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
            'ezpublish.api.repository.factory',
            'registerExternalStorageHandler',
            array( $storageHandlerServiceId, $fieldTypeIdentifier )
        );
    }
}
