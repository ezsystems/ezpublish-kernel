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
use PHPUnit\Framework\TestCase;

class MatcherBuilderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->createMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');
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
        $matcher = $this->createMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\Matcher');
        $builtMatcher = $matcherBuilder->buildMatcher('\\' . get_class($matcher), array(), new SimplifiedRequest());
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
            ->will($this->returnValue($this->createMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\Matcher')));
        $matcherBuilder = new MatcherBuilder($this->container);
        $matcherBuilder->buildMatcher("@$serviceId", array(), new SimplifiedRequest());
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\MatcherBuilder::buildMatcher
     */
    public function testBuildMatcherService()
    {
        $serviceId = 'foo';
        $matcher = $this->createMock('eZ\\Bundle\\EzPublishCoreBundle\\SiteAccess\\Matcher');
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($serviceId)
            ->will($this->returnValue($matcher));

        $matchingConfig = array('foo' => 'bar');
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
