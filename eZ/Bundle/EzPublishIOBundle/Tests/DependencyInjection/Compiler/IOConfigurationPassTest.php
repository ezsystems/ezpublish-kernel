<?php
/**
 * File containing the IOHandlerTagPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler\IOConfigurationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class IOConfigurationPassTest extends AbstractCompilerPassTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->container->setParameter( 'ez_io.metadata_handlers', array() );
        $this->container->setParameter( 'ez_io.binarydata_handlers', array() );

        $this->container->setParameter(
            'ez_io.available_metadata_handler_types',
            array(
                'flysystem' => 'ezpublish.core.io.metadata_handler.flysystem',
                'legacy_dfs_cluster' => 'ezpublish.core.io.metadata_handler.legacy_dfs_cluster'
            )
        );

        $this->container->setParameter(
            'ez_io.available_binarydata_handler_types',
            array(
                'flysystem' => 'ezpublish.core.io.binarydata_handler.flysystem'
            )
        );

        $this->container->setDefinition( 'ezpublish.core.io.metadata_handler.flysystem', new Definition() );
        $this->container->setDefinition( 'ezpublish.core.io.metadata_handler.legacy_dfs_cluster', new Definition() );
        $this->container->setDefinition( 'ezpublish.core.io.binarydata_handler.flysystem', new Definition() );

        $this->container->setDefinition( 'ezpublish.core.io.binarydata_handler.factory', new Definition() );
        $this->container->setDefinition( 'ezpublish.core.io.metadata_handler.factory', new Definition() );
    }

    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new IOConfigurationPass() );
    }

    /**
     * Tests that the default handlers are available when nothing is configured
     */
    public function testDefaultHandlers()
    {
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.core.io.binarydata_handler.factory',
            'setHandlersMap',
            array( array( 'default' => 'ezpublish.core.io.binarydata_handler.flysystem.default' ) )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.core.io.metadata_handler.factory',
            'setHandlersMap',
            array( array( 'default' => 'ezpublish.core.io.metadata_handler.flysystem.default' ) )
        );
    }

    public function testFlysystemBinaryHandler()
    {
        $this->container->setParameter(
            'ez_io.binarydata_handlers',
            array( 'flysystem' => array( 'my_handler' => array( 'adapter' => 'my_adapter' ) ) )
        );

        $this->container->setDefinition( 'oneup_flysystem.my_adapter_adapter', new Definition() );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithParent(
            'ezpublish.core.io.binarydata_handler.flysystem.my_handler',
            'ezpublish.core.io.binarydata_handler.flysystem'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.core.io.binarydata_handler.flysystem.my_handler',
            0,
            'ezpublish.core.io.flysystem.my_handler_filesystem'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithParent(
            'ezpublish.core.io.flysystem.my_handler_filesystem',
            'ezpublish.core.io.flysystem.base_filesystem'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.core.io.flysystem.my_handler_filesystem',
            0,
            'oneup_flysystem.my_adapter_adapter'
        );
    }

    public function testFlysystemMetadataHandler()
    {
        $this->container->setParameter(
            'ez_io.metadata_handlers',
            array( 'flysystem' => array( 'my_handler' => array( 'adapter' => 'my_adapter' ) ) )
        );

        $this->container->setDefinition( 'oneup_flysystem.my_adapter_adapter', new Definition() );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithParent(
            'ezpublish.core.io.metadata_handler.flysystem.my_handler',
            'ezpublish.core.io.metadata_handler.flysystem'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.core.io.metadata_handler.flysystem.my_handler',
            0,
            'ezpublish.core.io.flysystem.my_handler_filesystem'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithParent(
            'ezpublish.core.io.flysystem.my_handler_filesystem',
            'ezpublish.core.io.flysystem.base_filesystem'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.core.io.flysystem.my_handler_filesystem',
            0,
            'oneup_flysystem.my_adapter_adapter'
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
            array( 'unknown' => array() )
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
            array( 'unknown' => array() )
        );

        $this->compile();
    }
}
