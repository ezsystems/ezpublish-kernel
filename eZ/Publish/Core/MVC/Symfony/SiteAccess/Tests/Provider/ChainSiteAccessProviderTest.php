<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Test\Provider;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccessGroup;
use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\ChainSiteAccessProvider;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\StaticSiteAccessProvider;
use function array_map;

final class ChainSiteAccessProviderTest extends TestCase
{
    private const EXISTING_SA_NAME = 'existing_sa';
    private const UNDEFINED_SA_NAME = 'undefined_sa';
    private const SA_GROUP = 'group';

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface[] */
    private $providers;

    /** @var array */
    private $groupsBySiteAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupsBySiteAccess = [
            self::EXISTING_SA_NAME => [self::SA_GROUP],
            'first_sa' => [self::SA_GROUP],
            'second_sa' => [self::SA_GROUP],
        ];
        $this->providers = [
            new StaticSiteAccessProvider([self::EXISTING_SA_NAME, 'first_sa'], $this->groupsBySiteAccess),
            new StaticSiteAccessProvider(['second_sa'], $this->groupsBySiteAccess),
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
            $expectedSiteAccess = $this->createSiteAcccess(
                $expectedSiteAccessName, [self::SA_GROUP]
            );

            $this->assertEquals($expectedSiteAccess, $siteAccesses[$key]);
        }

        $undefinedSiteAccess = $this->createSiteAcccess(
            self::UNDEFINED_SA_NAME, [self::SA_GROUP]
        );

        $this->assertNotContains(
            $undefinedSiteAccess,
            $siteAccesses
        );
    }

    public function testGetExistingSiteAccess(): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();
        $expectedSiteAccess = new SiteAccess(
            self::EXISTING_SA_NAME,
            SiteAccess::DEFAULT_MATCHING_TYPE,
            null,
            StaticSiteAccessProvider::class
        );
        $expectedSiteAccess->groups = [self::SA_GROUP];
        $this->assertEquals(
            $expectedSiteAccess,
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

    /**
     * @param string[] $groupNames
     */
    private function createSiteAcccess(string $name, array $groupNames): SiteAccess
    {
        $undefinedSiteAccess = new SiteAccess(
            $name,
            SiteAccess::DEFAULT_MATCHING_TYPE,
            null,
            StaticSiteAccessProvider::class
        );
        $undefinedSiteAccess->groups = array_map(
            static function (string $groupName) {
                return new SiteAccessGroup($groupName);
            },
            $groupNames
        );

        return $undefinedSiteAccess;
    }
}
