<?php

/**
 * File containing the IOConfigurationPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection\Compiler;

use ArrayObject;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler\IOConfigurationPass;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class IOConfigurationPassTest extends AbstractCompilerPassTestCase
{
    /** @var ConfigurationFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $metadataConfigurationFactoryMock;

    /** @var ConfigurationFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $binarydataConfigurationFactoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->container->setParameter('ez_io.metadata_handlers', array());
        $this->container->setParameter('ez_io.binarydata_handlers', array());

        $this->container->setDefinition('ezpublish.core.io.binarydata_handler.factory', new Definition());
        $this->container->setDefinition('ezpublish.core.io.metadata_handler.factory', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $this->metadataConfigurationFactoryMock = $this->getMock('\eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory');
        $this->binarydataConfigurationFactoryMock = $this->getMock('\eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory');

        $container->addCompilerPass(
            new IOConfigurationPass(
                new ArrayObject(
                    array('test_handler' => $this->metadataConfigurationFactoryMock)
                ),
                new ArrayObject(
                    array('test_handler' => $this->binarydataConfigurationFactoryMock)
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
            array(array('default' => 'ezpublish.core.io.binarydata_handler.flysystem.default'))
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.core.io.metadata_handler.factory',
            'setHandlersMap',
            array(array('default' => 'ezpublish.core.io.metadata_handler.flysystem.default'))
        );
    }

    public function testBinarydataHandler()
    {
        $this->container->setParameter(
            'ez_io.binarydata_handlers',
            array('my_handler' => array('name' => 'my_handler', 'type' => 'test_handler'))
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
            array('my_handler' => array('name' => 'my_handler', 'type' => 'test_handler'))
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
            array('test' => array('type' => 'unknown'))
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
            array('test' => array('type' => 'unknown'))
        );

        $this->compile();
    }
}
