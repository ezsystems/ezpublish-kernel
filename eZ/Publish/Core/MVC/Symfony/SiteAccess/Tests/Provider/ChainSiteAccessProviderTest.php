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
    private const WITHOUT_GROUP_SA_NAME = 'without_group_sa';
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
            new StaticSiteAccessProvider([self::WITHOUT_GROUP_SA_NAME]),
        ];
    }

    public function isDefinedProvider(): array
    {
        return [
            'existing_sa' => [self::EXISTING_SA_NAME],
            'sa_without_group' => [self::WITHOUT_GROUP_SA_NAME],
        ];
    }

    /**
     * @dataProvider isDefinedProvider
     */
    public function testIsDefined(string $siteAccessName): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();

        $this->assertTrue($chainSiteAccessProvider->isDefined($siteAccessName));
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

        $this->assertCount(4, $siteAccesses);

        $expectedSiteAccessNames = [
            ['name' => self::EXISTING_SA_NAME, 'groups' => [self::SA_GROUP]],
            ['name' => 'first_sa', 'groups' => [self::SA_GROUP]],
            ['name' => 'second_sa', 'groups' => [self::SA_GROUP]],
            ['name' => self::WITHOUT_GROUP_SA_NAME, 'groups' => []],
        ];

        foreach ($expectedSiteAccessNames as $key => $saData) {
            $expectedSiteAccess = $this->createSiteAcccess(
                $saData['name'], $saData['groups']
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

    public function getExistingSiteProvider(): array
    {
        return [
            'existing_sa' => [self::EXISTING_SA_NAME, [self::SA_GROUP]],
            'sa_without_group' => [self::WITHOUT_GROUP_SA_NAME, []],
        ];
    }

    /**
     * @dataProvider getExistingSiteProvider
     *
     * @param string[] $expectedGroups
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetExistingSiteAccess(string $siteAccessName, array $expectedGroups): void
    {
        $chainSiteAccessProvider = $this->getChainSiteAccessProvider();
        $expectedSiteAccess = new SiteAccess(
            $siteAccessName,
            SiteAccess::DEFAULT_MATCHING_TYPE,
            null,
            StaticSiteAccessProvider::class
        );
        $expectedSiteAccess->groups = $expectedGroups;

        $this->assertEquals(
            $expectedSiteAccess,
            $chainSiteAccessProvider->getSiteAccess($siteAccessName)
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
    private function createSiteAcccess(string $name, array $groupNames = []): SiteAccess
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
