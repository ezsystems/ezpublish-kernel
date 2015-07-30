<?php

/**
 * File containing the LegacyStorageEnginePassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LegacyStorageEnginePassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.api.storage_engine.legacy.factory', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LegacyStorageEnginePass());
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass::process
     */
    public function testRegisterFieldType()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $def->addTag('ezpublish.fieldType', array('alias' => $fieldTypeIdentifier));
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.legacy.factory',
            'registerFieldType',
            array($serviceId, $fieldTypeIdentifier)
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass::process
     *
     * @expectedException \LogicException
     */
    public function testRegisterFieldTypeNoAlias()
    {
        $def = new Definition();
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $def->addTag('ezpublish.fieldType');
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.legacy.factory',
            'registerFieldType',
            array($serviceId, $fieldTypeIdentifier)
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass::process
     */
    public function testRegisterConverter()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';
        $callback = '::foobar';

        $def = new Definition();
        $def->setClass($class);
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array(
                'alias' => $fieldTypeIdentifier,
                'lazy' => true,
                'callback' => $callback,
            )
        );
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.legacy.factory',
            'registerFieldTypeConverter',
            array($fieldTypeIdentifier, $class . $callback)
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass::process
     *
     * @expectedException \LogicException
     */
    public function testRegisterConverterNoAlias()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';
        $callback = '::foobar';

        $def = new Definition();
        $def->setClass($class);
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array(
                'lazy' => true,
                'callback' => $callback,
            )
        );
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.legacy.factory',
            'registerFieldTypeConverter',
            array($fieldTypeIdentifier, $class . $callback)
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass::process
     *
     * @expectedException \LogicException
     */
    public function testRegisterConverterLazyNoCallback()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';
        $callback = '::foobar';

        $def = new Definition();
        $def->setClass($class);
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array(
                'alias' => $fieldTypeIdentifier,
                'lazy' => true,
            )
        );
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.legacy.factory',
            'registerFieldTypeConverter',
            array($fieldTypeIdentifier, $class . $callback)
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LegacyStorageEnginePass::process
     */
    public function testRegisterConverterNoLazy()
    {
        $fieldTypeIdentifier = 'fieldtype_identifier';
        $serviceId = 'some_service_id';
        $class = 'Some\Class';

        $def = new Definition();
        $def->setClass($class);
        $def->addTag(
            'ezpublish.storageEngine.legacy.converter',
            array('alias' => $fieldTypeIdentifier)
        );
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.legacy.factory',
            'registerFieldTypeConverter',
            array($fieldTypeIdentifier, new Reference($serviceId))
        );
    }
}
