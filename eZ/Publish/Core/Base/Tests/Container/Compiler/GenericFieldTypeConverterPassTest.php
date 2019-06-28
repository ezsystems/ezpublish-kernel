<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Base\Tests\Container\Compiler;

use eZ\Publish\Core\Base\Container\Compiler\FieldTypeCollectionPass;
use eZ\Publish\Core\Base\Container\Compiler\GenericFieldTypeConverterPass;
use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\FieldValueConverterRegistryPass;
use eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs\GenericFieldType;
use eZ\Publish\Core\FieldType\Generic\Type;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasMethodCallConstraint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GenericFieldTypeConverterPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(FieldValueConverterRegistryPass::CONVERTER_REGISTRY_SERVICE_ID, new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new GenericFieldTypeConverterPass());
    }

    public function testFieldValueConverterIsAutoRegistered(): void
    {
        $fieldTypeIdentifier = 'field_type_identifier';

        $fieldTypeDefinition = new Definition();
        $fieldTypeDefinition->setClass(GenericFieldType::class);
        $fieldTypeDefinition->addTag(
            FieldTypeCollectionPass::FIELD_TYPE_SERVICE_TAG,
            [
                'alias' => $fieldTypeIdentifier,
            ]
        );

        $this->setDefinition('field_type', $fieldTypeDefinition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            FieldValueConverterRegistryPass::CONVERTER_REGISTRY_SERVICE_ID,
            'register',
            [
                $fieldTypeIdentifier,
                new Reference(GenericFieldTypeConverterPass::GENERIC_CONVERTER_SERVICE_ID),
            ]
        );
    }

    public function testFieldValueConverterIsNotAutoRegisteredIfExplicitlyProvided(): void
    {
        $fieldTypeIdentifier = 'field_type_identifier';

        $fieldTypeDefinition = new Definition();
        $fieldTypeDefinition->setClass(GenericFieldType::class);
        $fieldTypeDefinition->addTag(
            FieldTypeCollectionPass::FIELD_TYPE_SERVICE_TAG,
            [
                'alias' => $fieldTypeIdentifier,
            ]
        );

        $fieldValueConverterDefinition = new Definition();
        $fieldValueConverterDefinition->setClass('MyCustomFieldValueConverter');
        $fieldValueConverterDefinition->addTag(
            FieldValueConverterRegistryPass::CONVERTER_SERVICE_TAG,
            [
                'alias' => $fieldTypeIdentifier,
            ]
        );

        $this->setDefinition('field_type', $fieldTypeDefinition);
        $this->setDefinition('field_value_converter', $fieldValueConverterDefinition);
        $this->compile();

        $this->assertContainerBuilderHasNoServiceDefinitionWithMethodCall(
            FieldValueConverterRegistryPass::CONVERTER_REGISTRY_SERVICE_ID,
            'register',
            [
                $fieldTypeIdentifier,
                new Reference(GenericFieldTypeConverterPass::GENERIC_CONVERTER_SERVICE_ID),
            ]
        );
    }

    public function testFieldValueConverterIsNotAutoRegisteredIfFieldTypeIsNotBasedOnGeneric(): void
    {
        $fieldTypeIdentifier = 'field_type_identifier';

        $fieldTypeDefinition = new Definition();
        $fieldTypeDefinition->setClass(Type::class);
        $fieldTypeDefinition->addTag(
            FieldTypeCollectionPass::FIELD_TYPE_SERVICE_TAG,
            [
                'alias' => $fieldTypeIdentifier,
            ]
        );

        $this->setDefinition('field_type', $fieldTypeDefinition);
        $this->compile();

        $this->assertContainerBuilderHasNoServiceDefinitionWithMethodCall(
            FieldValueConverterRegistryPass::CONVERTER_REGISTRY_SERVICE_ID,
            'register',
            [
                $fieldTypeIdentifier,
                new Reference(GenericFieldTypeConverterPass::GENERIC_CONVERTER_SERVICE_ID),
            ]
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has NO service definition with the given id, which has a method
     * call to the given method with the given arguments.
     *
     * @see AbstractContainerBuilderTestCase::assertContainerBuilderHasServiceDefinitionWithMethodCall
     */
    private function assertContainerBuilderHasNoServiceDefinitionWithMethodCall(
        string $serviceId,
        string $method,
        array $arguments = [],
        $index = null
    ): void {
        self::assertThat(
            $this->container->findDefinition($serviceId),
            self::logicalNot(new DefinitionHasMethodCallConstraint($method, $arguments, $index))
        );
    }
}
