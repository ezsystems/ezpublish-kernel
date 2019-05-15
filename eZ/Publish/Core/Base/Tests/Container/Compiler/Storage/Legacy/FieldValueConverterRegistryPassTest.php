<?php

/**
 * File containing the FieldValueConverterRegistryPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage\Legacy;

use eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy\FieldValueConverterRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FieldValueConverterRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setDefinition('ezpublish.persistence.legacy.field_value_converter.registry', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FieldValueConverterRegistryPass());
    }

    public function testRegisterConverterNoLazy()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';

        $def = new Definition();
        $def->setClass($class);
        $def->addTag(
            'ezplatform.field_type.legacy_storage.converter',
            array('alias' => $fieldTypeIdentifier)
        );
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.legacy.field_value_converter.registry',
            'register',
            array($fieldTypeIdentifier, new Reference($serviceId))
        );
    }
}
