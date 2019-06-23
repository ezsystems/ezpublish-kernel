<?php

/**
 * File containing the IOConfigurationPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection\Compiler;

use ArrayObject;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler\IOConfigurationPass;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit\Framework\MockObject\MockObject;

class IOConfigurationPassTest extends AbstractCompilerPassTestCase
{
    /** @var ConfigurationFactory|MockObject */
    protected $metadataConfigurationFactoryMock;

    /** @var ConfigurationFactory|MockObject */
    protected $binarydataConfigurationFactoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->container->setParameter('ez_io.metadata_handlers', []);
        $this->container->setParameter('ez_io.binarydata_handlers', []);

        $this->container->setDefinition('ezpublish.core.io.binarydata_handler.factory', new Definition());
        $this->container->setDefinition('ezpublish.core.io.metadata_handler.factory', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $this->metadataConfigurationFactoryMock = $this->createMock(ConfigurationFactory::class);
        $this->binarydataConfigurationFactoryMock = $this->createMock(ConfigurationFactory::class);

        $container->addCompilerPass(
            new IOConfigurationPass(
                new ArrayObject(
                    ['test_handler' => $this->metadataConfigurationFactoryMock]
                ),
                new ArrayObject(
                    ['test_handler' => $this->binarydataConfigurationFactoryMock]
                )
            )
        );
    }

    /**
     * Tests that the default handlers are available when nothing is configured.
     */
    public function testDefaultHandlers()
    {
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.core.io.binarydata_handler.factory',
            'setHandlersMap',
            [['default' => 'ezpublish.core.io.binarydata_handler.flysystem.default']]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.core.io.metadata_handler.factory',
            'setHandlersMap',
            [['default' => 'ezpublish.core.io.metadata_handler.flysystem.default']]
        );
    }

    public function testBinarydataHandler()
    {
        $this->container->setParameter(
            'ez_io.binarydata_handlers',
            ['my_handler' => ['name' => 'my_handler', 'type' => 'test_handler']]
        );

        $this->binarydataConfigurationFactoryMock
            ->expects($this->once())
            ->method('getParentServiceId')
            ->will($this->returnValue('test.io.binarydata_handler.test_handler'));

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithParent(
            'test.io.binarydata_handler.test_handler.my_handler',
            'test.io.binarydata_handler.test_handler'
        );
    }

    public function testMetadataHandler()
    {
        $this->container->setParameter(
            'ez_io.metadata_handlers',
            ['my_handler' => ['name' => 'my_handler', 'type' => 'test_handler']]
        );

        $this->metadataConfigurationFactoryMock
            ->expects($this->once())
            ->method('getParentServiceId')
            ->will($this->returnValue('test.io.metadata_handler.test_handler'));

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithParent(
            'test.io.metadata_handler.test_handler.my_handler',
            'test.io.metadata_handler.test_handler'
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Unknown handler
     */
    public function testUnknownMetadataHandler()
    {
        $this->container->setParameter(
            'ez_io.metadata_handlers',
            ['test' => ['type' => 'unknown']]
        );

        $this->compile();
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Unknown handler
     */
    public function testUnknownBinarydataHandler()
    {
        $this->container->setParameter(
            'ez_io.binarydata_handlers',
            ['test' => ['type' => 'unknown']]
        );

        $this->compile();
    }
}
