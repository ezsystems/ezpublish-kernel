<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;

abstract class RouterBaseTest extends TestCase
{
    protected const UNDEFINED_SA_NAME = 'undefined_sa';
    protected const ENV_SA_NAME = 'env_sa';
    protected const HEADERBASED_SA_NAME = 'headerbased_sa';

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder */
    protected $matcherBuilder;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface */
    protected $siteAccessProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcherBuilder = new MatcherBuilder();
        $this->siteAccessProvider = $this->createSiteAccessProviderMock();
    }

    public function testConstruct(): Router
    {
        return $this->createRouter();
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatch(SimplifiedRequest $request, string $siteAccess)
    {
        $router = $this->createRouter();
        $sa = $router->match($request);
        $this->assertInstanceOf(SiteAccess::class, $sa);
        $this->assertSame($siteAccess, $sa->name);
        // SiteAccess must be serializable as a whole. See https://jira.ez.no/browse/EZP-21613
        $this->assertIsString(serialize($sa));
        $router->setSiteAccess();
    }

    abstract public function matchProvider(): array;

    abstract protected function createRouter(): Router;

    private function createSiteAccessProviderMock(): SiteAccess\SiteAccessProviderInterface
    {
        $isDefinedMap = [];
        $getSiteAccessMap = [];
        foreach ($this->getSiteAccessProviderSettings() as $sa) {
            $isDefinedMap[] = [$sa->name, $sa->isDefined];
            $getSiteAccessMap[] = [
                $sa->name,
                new SiteAccess(
                    $sa->name,
                    $sa->matchingType
                ),
            ];
        }
        $siteAccessProviderMock = $this->createMock(SiteAccess\SiteAccessProviderInterface::class);
        $siteAccessProviderMock
            ->method('isDefined')
            ->willReturnMap($isDefinedMap);
        $siteAccessProviderMock
            ->method('getSiteAccess')
            ->willReturnMap($getSiteAccessMap);

        return $siteAccessProviderMock;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\SiteAccessSetting[]
     */
    abstract public function getSiteAccessProviderSettings(): array;
}
