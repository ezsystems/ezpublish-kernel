<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\EzPublishIOExtension;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class EzPublishIOExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        $extension = new EzPublishIOExtension();
        $extension->addMetadataHandlerFactory( 'flysystem', new ConfigurationFactory\MetadataHandler\Flysystem() );
        $extension->addBinarydataHandlerFactory( 'flysystem', new ConfigurationFactory\BinarydataHandler\Flysystem() );

        return array( $extension );
    }

    public function testParametersWithoutConfiguration()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter( 'ez_io.metadata_handlers', array() );
        $this->assertContainerBuilderHasParameter( 'ez_io.binarydata_handlers', array() );
    }

    public function testParametersWithMetadataHandler()
    {
        $config = array(
            'metadata_handlers' => array(
                'my_metadata_handler' => array( 'flysystem' => array( 'adapter' => 'my_adapter' ) )
            )
        );
        $this->load( $config );

        $this->assertContainerBuilderHasParameter( 'ez_io.binarydata_handlers', array() );
        $this->assertContainerBuilderHasParameter(
            'ez_io.metadata_handlers',
            array( 'my_metadata_handler' => array( 'name' => 'my_metadata_handler', 'type' => 'flysystem', 'adapter' => 'my_adapter' ) )
        );
    }

    public function testParametersWithBinarydataHandler()
    {
        $config = array(
            'binarydata_handlers' => array(
                'my_binarydata_handler' => array( 'flysystem' => array( 'adapter' => 'my_adapter' ) )
            )
        );
        $this->load( $config );

        $this->assertContainerBuilderHasParameter( 'ez_io.metadata_handlers', array() );
        $this->assertContainerBuilderHasParameter(
            'ez_io.binarydata_handlers',
            array( 'my_binarydata_handler' => array( 'name' => 'my_binarydata_handler', 'type' => 'flysystem', 'adapter' => 'my_adapter' ) )
        );
    }
}
