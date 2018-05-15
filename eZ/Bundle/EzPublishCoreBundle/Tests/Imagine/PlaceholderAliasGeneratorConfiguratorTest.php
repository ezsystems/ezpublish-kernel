<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderAliasGenerator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderAliasGeneratorConfigurator;
use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;
use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

class PlaceholderAliasGeneratorConfiguratorTest extends TestCase
{
    const BINARY_HANDLER_NAME = 'default';
    const PROVIDER_TYPE = 'generic';
    const PROVIDER_OPTIONS = [
        'a' => 'A',
        'b' => 'B',
        'c' => 'C',
    ];

    public function testConfigure()
    {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('io.binarydata_handler')
            ->willReturn(self::BINARY_HANDLER_NAME);

        $provider = $this->createMock(PlaceholderProvider::class);

        $providerRegistry = $this->createMock(PlaceholderProviderRegistry::class);
        $providerRegistry
            ->expects($this->once())
            ->method('getProvider')
            ->with(self::PROVIDER_TYPE)
            ->willReturn($provider);

        $providerConfig = [
            self::BINARY_HANDLER_NAME => [
                'provider' => self::PROVIDER_TYPE,
                'options' => self::PROVIDER_OPTIONS,
            ],
        ];

        $generator = $this->createMock(PlaceholderAliasGenerator::class);
        $generator
            ->expects($this->once())
            ->method('setPlaceholderProvider')
            ->with($provider, self::PROVIDER_OPTIONS);

        $configurator = new PlaceholderAliasGeneratorConfigurator(
            $configResolver,
            $providerRegistry,
            $providerConfig
        );
        $configurator->configure($generator);
    }
}
