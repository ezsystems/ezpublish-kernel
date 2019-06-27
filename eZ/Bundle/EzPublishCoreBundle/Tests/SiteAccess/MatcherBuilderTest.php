<?php

/**
 * File containing the MatcherBuilderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\SiteAccess;

use eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher as CoreMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MatcherBuilderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::buildMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder::buildMatcher
     */
    public function testBuildMatcherNoService()
    {
        $this->container
            ->expects($this->never())
            ->method('get');
        $matcherBuilder = new MatcherBuilder($this->container);
        $matcher = $this->createMock(Matcher::class);
        $builtMatcher = $matcherBuilder->buildMatcher('\\' . get_class($matcher), [], new SimplifiedRequest());
        $this->assertInstanceOf(get_class($matcher), $builtMatcher);
    }

    /**
     * @expectedException \RuntimeException
     *
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::buildMatcher
     */
    public function testBuildMatcherServiceWrongInterface()
    {
        $serviceId = 'foo';
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($serviceId)
            ->will($this->returnValue($this->createMock(Matcher::class)));
        $matcherBuilder = new MatcherBuilder($this->container);
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
        $this->container
            ->expects($this->once())
            ->method('get')
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

        $matcherBuilder = new MatcherBuilder($this->container);
        $matcherBuilder->buildMatcher("@$serviceId", $matchingConfig, $request);
    }
}
