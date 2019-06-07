<?php

/**
 * File containing the FieldTypeCollectionPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler;

use eZ\Publish\Core\Base\Container\Compiler\FieldTypeCollectionPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FieldTypeCollectionPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setDefinition('ezpublish.field_type_collection.factory', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FieldTypeCollectionPass());
    }

    /**
     * @dataProvider tagsProvider
     */
    public function testRegisterFieldType(string $tag)
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag($tag, ['alias' => $fieldTypeIdentifier]);
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.field_type_collection.factory',
            'registerFieldType',
            [$serviceId, $fieldTypeIdentifier]
        );
    }

    /**
     * @dataProvider tagsProvider
     *
     * @param string $tag
     */
    public function testRegisterFieldTypeNoAlias(string $tag)
    {
        $this->expectException(\LogicException::class);

        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag($tag);
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.field_type_collection.factory',
            'registerFieldType',
            [$serviceId, $fieldTypeIdentifier]
        );
    }

    public function tagsProvider(): array
    {
        return [
            [FieldTypeCollectionPass::DEPRECATED_FIELD_TYPE_SERVICE_TAG],
            [FieldTypeCollectionPass::FIELD_TYPE_SERVICE_TAG],
        ];
    }
}
