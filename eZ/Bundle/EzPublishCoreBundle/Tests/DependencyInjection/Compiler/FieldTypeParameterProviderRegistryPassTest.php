<?php

/**
 * File containing the FieldTypeParameterProviderRegistryPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\FieldTypeParameterProviderRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FieldTypeParameterProviderRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.fieldType.parameterProviderRegistry', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FieldTypeParameterProviderRegistryPass());
    }

    public function testRegisterFieldType()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.fieldType.parameterProvider', ['alias' => $fieldTypeIdentifier]);
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.parameterProviderRegistry',
            'setParameterProvider',
            [new Reference($serviceId), $fieldTypeIdentifier]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterFieldTypeNoAlias()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.fieldType.parameterProvider');
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.fieldType.parameterProviderRegistry',
            'setParameterProvider',
            [new Reference($serviceId), $fieldTypeIdentifier]
        );
    }
}
