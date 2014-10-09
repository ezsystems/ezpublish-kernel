<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\EzPublishIOExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class EzPublishIOExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new EzPublishIOExtension()
        );
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
                'test' => array( 'flysystem' => array( 'adapter' => 'test' ) )
            )
        );
        $this->load( $config );

        $this->assertContainerBuilderHasParameter( 'ez_io.binarydata_handlers', array() );
        $this->assertContainerBuilderHasParameter(
            'ez_io.metadata_handlers',
            array( 'flysystem' => array( 'test' => array( 'adapter' => 'test' ) ) )
        );
    }

    public function testParametersWithBinarydataHandler()
    {
        $config = array(
            'binarydata_handlers' => array(
                'test' => array( 'flysystem' => array( 'adapter' => 'test' ) )
            )
        );
        $this->load( $config );

        $this->assertContainerBuilderHasParameter( 'ez_io.metadata_handlers', array() );
        $this->assertContainerBuilderHasParameter(
            'ez_io.binarydata_handlers',
            array( 'flysystem' => array( 'test' => array( 'adapter' => 'test' ) ) )
        );
    }
}
