<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine;

use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;
use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry;
use PHPUnit\Framework\TestCase;

class PlaceholderProviderRegistryTest extends TestCase
{
    private const FOO = 'foo';
    private const BAR = 'bar';

    /**
     * @covers       \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry::__construct
     *
     * @uses         \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry::getProvider
     * @depends      testGetProviderKnown
     */
    public function testConstructor()
    {
        $providers = [
            self::FOO => $this->getPlaceholderProviderMock(),
            self::BAR => $this->getPlaceholderProviderMock(),
        ];

        $registry = new PlaceholderProviderRegistry($providers);

        $this->assertSame($providers[self::FOO], $registry->getProvider(self::FOO));
        $this->assertSame($providers[self::BAR], $registry->getProvider(self::BAR));
    }

    /**
     * @covers       \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry::addProvider
     *
     * @uses         \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProviderRegistry::getProvider
     * @depends      testGetProviderKnown
     */
    public function testAddProvider(): void
    {
        $provider = $this->getPlaceholderProviderMock();

        $registry = new PlaceholderProviderRegistry();
        $registry->addProvider(self::FOO, $provider);

        $this->assertSame($provider, $registry->getProvider(self::FOO));
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
            self::FOO => $provider,
        ]);

        $this->assertEquals($provider, $registry->getProvider(self::FOO));
    }

    public function testGetProviderUnknown()
    {
        $this->expectException(\InvalidArgumentException::class);

        $registry = new PlaceholderProviderRegistry([
            self::FOO => $this->getPlaceholderProviderMock(),
        ]);

        $registry->getProvider(self::BAR);
    }

    private function getPlaceholderProviderMock(): PlaceholderProvider
    {
        return $this->createMock(PlaceholderProvider::class);
    }
}
