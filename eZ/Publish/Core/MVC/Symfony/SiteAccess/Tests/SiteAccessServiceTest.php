<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use ArrayIterator;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService;
use PHPUnit\Framework\TestCase;

class SiteAccessServiceTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $provider;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    /** @var \ArrayIterator */
    private $availableSiteAccesses;

    /** @var array */
    private $configResolverParameters;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = $this->createMock(SiteAccessProviderInterface::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->siteAccess = new SiteAccess('current');
        $this->availableSiteAccesses = $this->getAvailableSitAccesses(['current', 'first_sa', 'second_sa', 'default']);
        $this->configResolverParameters = $this->getConfigResolverParameters();
    }

    public function testGetCurrentSiteAccess(): void
    {
        $this->assertSame($this->siteAccess, $this->getSiteAccessService()->getCurrent());
    }

    public function testGetCurrentSiteAccessesRelation(): void
    {
        $this->configResolver
            ->method('getParameter')
            ->willReturnMap($this->configResolverParameters);

        $this->provider
            ->method('getSiteAccesses')
            ->willReturn($this->availableSiteAccesses);

        $this->assertSame(['current', 'first_sa'], $this->getSiteAccessService()->getSiteAccessesRelation());
    }

    public function testGetFirstSiteAccessesRelation(): void
    {
        $this->configResolver
            ->method('getParameter')
            ->willReturnMap($this->configResolverParameters);

        $this->provider
            ->method('getSiteAccesses')
            ->willReturn($this->availableSiteAccesses);

        $this->assertSame(
            ['current', 'first_sa'],
            $this->getSiteAccessService()->getSiteAccessesRelation(new SiteAccess('first_sa'))
        );
    }

    private function getSiteAccessService(): SiteAccessService
    {
        $siteAccessService = new SiteAccessService($this->provider, $this->configResolver);
        $siteAccessService->setSiteAccess($this->siteAccess);

        return $siteAccessService;
    }

    /**
     * @param string[] $siteAccessNames
     */
    private function getAvailableSitAccesses(array $siteAccessNames): ArrayIterator
    {
        $availableSitAccesses = [];
        foreach ($siteAccessNames as $siteAccessName) {
            $availableSitAccesses[] = new SiteAccess($siteAccessName);
        }

        return new ArrayIterator($availableSitAccesses);
    }

    private function getConfigResolverParameters(): array
    {
        return [
            ['repository', 'ezsettings', 'current', 'repository_1'],
            ['content.tree_root.location_id', 'ezsettings', 'current', 1],
            ['repository', 'ezsettings', 'first_sa', 'repository_1'],
            ['content.tree_root.location_id', 'ezsettings', 'first_sa', 1],
            ['repository', 'ezsettings', 'second_sa', 'repository_1'],
            ['content.tree_root.location_id', 'ezsettings', 'second_sa', 2],
            ['repository', 'ezsettings', 'default', ''],
            ['content.tree_root.location_id', 'ezsettings', 'default', 3],
        ];
    }
}
