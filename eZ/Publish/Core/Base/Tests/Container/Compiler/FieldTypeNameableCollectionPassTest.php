<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler;

use eZ\Publish\Core\Base\Container\Compiler\FieldTypeNameableCollectionPass;
use eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\FieldType;
use eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\NameableFieldType;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FieldTypeNameableCollectionPassTest extends AbstractCompilerPassTestCase
{
    private const FIELD_TYPE_NAMEABLE_COLLECTION_FACTORY = 'ezpublish.field_type_nameable_collection.factory';
    private const FIELD_TYPE_COLLECTION_FACTORY = 'ezpublish.field_type_collection.factory';

    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition(self::FIELD_TYPE_NAMEABLE_COLLECTION_FACTORY, new Definition());
        $this->setDefinition(self::FIELD_TYPE_COLLECTION_FACTORY, new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FieldTypeNameableCollectionPass());
    }

    public function testRegisterFieldType(): void
    {
        $nameableFieldTypeIdentifier = 'nameable_field_type_identifier';
        $nameableServiceId = 'nameable_service_id';
        $def = new Definition();
        $def->setClass(NameableFieldType::class);
        $def->addTag('ezpublish.fieldType', array('alias' => $nameableFieldTypeIdentifier));
        $this->setDefinition($nameableServiceId, $def);

        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->setClass(FieldType::class);
        $def->addTag('ezpublish.fieldType', array('alias' => $fieldTypeIdentifier));
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::FIELD_TYPE_NAMEABLE_COLLECTION_FACTORY,
            'registerNameableFieldType',
            array($nameableServiceId, $nameableFieldTypeIdentifier)
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::FIELD_TYPE_NAMEABLE_COLLECTION_FACTORY,
            'registerNonNameableFieldType',
            array($serviceId, $fieldTypeIdentifier)
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterFieldTypeNoAlias(): void
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.fieldType');
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            self::FIELD_TYPE_NAMEABLE_COLLECTION_FACTORY,
            'registerNameableFieldType',
            array($serviceId, $fieldTypeIdentifier)
        );
    }
}
