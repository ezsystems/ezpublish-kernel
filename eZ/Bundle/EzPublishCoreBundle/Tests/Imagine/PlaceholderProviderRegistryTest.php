<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;
use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry;
use PHPUnit\Framework\TestCase;

class PlaceholderProviderRegistryTest extends TestCase
{
    public function testConstructor()
    {
        $providers = [
            'foo' => $this->getPlaceholderProviderMock(),
            'bar' => $this->getPlaceholderProviderMock(),
        ];

        $registry = new PlaceholderProviderRegistry($providers);

        $this->assertAttributeSame($providers, 'providers', $registry);
    }

    public function testAddProvider()
    {
        $provider = $this->getPlaceholderProviderMock();

        $registry = new PlaceholderProviderRegistry();
        $registry->addProvider('foo', $provider);

        $this->assertAttributeSame(['foo' => $provider], 'providers', $registry);
    }

    public function testSupports()
    {
        $registry = new PlaceholderProviderRegistry([
            'supported' => $this->getPlaceholderProviderMock(),
        ]);

        $this->assertTrue($registry->supports('supported'));
        $this->assertFalse($registry->supports('unsupported'));
    }

    public function testGetProviderKnown()
    {
        $provider = $this->getPlaceholderProviderMock();

        $registry = new PlaceholderProviderRegistry([
            'foo' => $provider,
        ]);

        $this->assertEquals($provider, $registry->getProvider('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetProviderUnknown()
    {
        $registry = new PlaceholderProviderRegistry([
            'foo' => $this->getPlaceholderProviderMock(),
        ]);

        $registry->getProvider('bar');
    }

    private function getPlaceholderProviderMock(): PlaceholderProvider
    {
        return $this->createMock(PlaceholderProvider::class);
    }
}
