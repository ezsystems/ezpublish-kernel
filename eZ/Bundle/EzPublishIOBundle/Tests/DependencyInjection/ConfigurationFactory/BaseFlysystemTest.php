<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection\ConfigurationFactory;

use eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection\ConfigurationFactoryTest;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class BaseFlysystemTest extends ConfigurationFactoryTest
{
    private $flysystemAdapterServiceId = 'oneup_flysystem.test_adapter';

    private $filesystemServiceId = 'ezpublish.core.io.flysystem.my_test_handler_filesystem';

    public function provideHandlerConfiguration()
    {
        $this->setDefinition($this->flysystemAdapterServiceId, new Definition());

        return [
            'adapter' => 'test',
        ];
    }

    public function provideParentServiceDefinition()
    {
        return new Definition(null, [null]);
    }

    public function validateConfiguredHandler($handlerDefinitionId)
    {
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            $handlerDefinitionId,
            0,
            new Reference($this->filesystemServiceId)
        );
    }

    public function validateConfiguredContainer()
    {
        self::assertContainerBuilderHasService(
            $this->filesystemServiceId
        );
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.core.io.flysystem.my_test_handler_filesystem',
            0,
            new Reference($this->flysystemAdapterServiceId)
        );
    }
}
