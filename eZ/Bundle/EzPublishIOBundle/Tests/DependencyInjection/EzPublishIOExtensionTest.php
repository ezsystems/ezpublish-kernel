<?php

/**
 * This file is part of the eZ Publish Kernel package.
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
        $extension->addMetadataHandlerFactory('flysystem', new ConfigurationFactory\MetadataHandler\Flysystem());
        $extension->addBinarydataHandlerFactory('flysystem', new ConfigurationFactory\BinarydataHandler\Flysystem());

        return [$extension];
    }

    public function testParametersWithoutConfiguration()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('ez_io.metadata_handlers', []);
        $this->assertContainerBuilderHasParameter('ez_io.binarydata_handlers', []);
    }

    public function testParametersWithMetadataHandler()
    {
        $config = [
            'metadata_handlers' => [
                'my_metadata_handler' => ['flysystem' => ['adapter' => 'my_adapter']],
            ],
        ];
        $this->load($config);

        $this->assertContainerBuilderHasParameter('ez_io.binarydata_handlers', []);
        $this->assertContainerBuilderHasParameter(
            'ez_io.metadata_handlers',
            ['my_metadata_handler' => ['name' => 'my_metadata_handler', 'type' => 'flysystem', 'adapter' => 'my_adapter']]
        );
    }

    public function testParametersWithBinarydataHandler()
    {
        $config = [
            'binarydata_handlers' => [
                'my_binarydata_handler' => ['flysystem' => ['adapter' => 'my_adapter']],
            ],
        ];
        $this->load($config);

        $this->assertContainerBuilderHasParameter('ez_io.metadata_handlers', []);
        $this->assertContainerBuilderHasParameter(
            'ez_io.binarydata_handlers',
            ['my_binarydata_handler' => ['name' => 'my_binarydata_handler', 'type' => 'flysystem', 'adapter' => 'my_adapter']]
        );
    }
}
