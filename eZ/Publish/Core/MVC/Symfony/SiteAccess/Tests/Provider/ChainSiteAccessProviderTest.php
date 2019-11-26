<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Test\Provider;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\ChainSiteAccessProvider;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\StaticSiteAccessProvider;

final class ChainSiteAccessProviderTest extends TestCase
{
    private const EXISTING_SA_NAME = 'existing_sa';
    private const UNDEFINED_SA_NAME = 'undefined_sa';

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface[] */
    private $providers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providers = [
            new StaticSiteAccessProvider([self::EXISTING_SA_NAME, 'first_sa']),
            new StaticSiteAccessProvider(['second_sa']),
        ];
    }

    public function testIsDefinedForExistingSiteAccess(): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();

        $this->assertTrue($chainSiteAccessProvider->isDefined(self::EXISTING_SA_NAME));
    }

    public function testIsDefinedForUndefinedSiteAccess(): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();

        $this->assertFalse($chainSiteAccessProvider->isDefined(self::UNDEFINED_SA_NAME));
    }

    public function testGetSiteAccesses(): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();
        $siteAccesses = iterator_to_array($chainSiteAccessProvider->getSiteAccesses());

        $this->assertCount(3, $siteAccesses);

        $expectedSiteAccessNames = [self::EXISTING_SA_NAME, 'first_sa', 'second_sa'];

        foreach ($expectedSiteAccessNames as $key => $expectedSiteAccessName) {
            $expectedSiteAccess = new SiteAccess(
                $expectedSiteAccessName,
                SiteAccess::DEFAULT_MATCHING_TYPE,
                null,
                StaticSiteAccessProvider::class
            );
            $this->assertEquals($expectedSiteAccess, $siteAccesses[$key]);
        }

        $this->assertNotContains(
            new SiteAccess(
                self::UNDEFINED_SA_NAME,
                SiteAccess::DEFAULT_MATCHING_TYPE,
                null,
                StaticSiteAccessProvider::class
            ),
            $siteAccesses
        );
    }

    public function testGetExistingSiteAccess(): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();
        $this->assertEquals(
            new SiteAccess(
                self::EXISTING_SA_NAME,
                SiteAccess::DEFAULT_MATCHING_TYPE,
                null,
                StaticSiteAccessProvider::class
            ),
            $chainSiteAccessProvider->getSiteAccess(self::EXISTING_SA_NAME)
        );
    }

    public function testGetUndefinedSiteAccess(): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Could not find 'Site Access' with identifier 'undefined_sa'");

        $chainSiteAccessProvider->getSiteAccess(self::UNDEFINED_SA_NAME);
    }

    private function getChainSiteAccessProvider(): ChainSiteAccessProvider
    {
        return new ChainSiteAccessProvider($this->providers);
    }
}
