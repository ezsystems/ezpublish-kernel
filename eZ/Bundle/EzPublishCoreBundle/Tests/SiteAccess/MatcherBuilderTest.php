<?php

/**
 * File containing the MatcherBuilderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\SiteAccess;

use eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder;
use eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessMatcherRegistryInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher as CoreMatcher;
use PHPUnit\Framework\TestCase;

class MatcherBuilderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $siteAccessMatcherRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siteAccessMatcherRegistry = $this->createMock(SiteAccessMatcherRegistryInterface::class);
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::buildMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder::buildMatcher
     */
    public function testBuildMatcherNoService()
    {
        $this->siteAccessMatcherRegistry
            ->expects($this->never())
            ->method('getMatcher');
        $matcherBuilder = new MatcherBuilder($this->siteAccessMatcherRegistry);
        $matcher = $this->createMock(Matcher::class);
        $builtMatcher = $matcherBuilder->buildMatcher('\\' . get_class($matcher), [], new SimplifiedRequest());
        $this->assertInstanceOf(get_class($matcher), $builtMatcher);
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::buildMatcher
     */
    public function testBuildMatcherServiceWrongInterface()
    {
        $this->expectException(\TypeError::class);

        $serviceId = 'foo';
        $this->siteAccessMatcherRegistry
            ->expects($this->once())
            ->method('getMatcher')
            ->with($serviceId)
            ->will($this->returnValue($this->createMock(Matcher::class)));
        $matcherBuilder = new MatcherBuilder($this->siteAccessMatcherRegistry);
        $matcherBuilder->buildMatcher("@$serviceId", [], new SimplifiedRequest());
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::buildMatcher
     */
    public function testBuildMatcherService()
    {
        $serviceId = 'foo';
        $matcher = $this->createMock(CoreMatcher::class);
        $this->siteAccessMatcherRegistry
            ->expects($this->once())
            ->method('getMatcher')
            ->with($serviceId)
            ->will($this->returnValue($matcher));

        $matchingConfig = ['foo' => 'bar'];
        $request = new SimplifiedRequest();
        $matcher
            ->expects($this->once())
            ->method('setMatchingConfiguration')
            ->with($matchingConfig);
        $matcher
            ->expects($this->once())
            ->method('setRequest')
            ->with($request);

        $matcherBuilder = new MatcherBuilder($this->siteAccessMatcherRegistry);
        $matcherBuilder->buildMatcher("@$serviceId", $matchingConfig, $request);
    }
}
