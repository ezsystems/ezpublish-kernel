<?php

/**
 * File containing the RouterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\RequestParser;

use PHPUnit\Framework\TestCase;
use eZ\Bundle\EzPublishRestBundle\RequestParser\Router as RouterRequestParser;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

class RouterTest extends TestCase
{
    /** @var \Symfony\Cmf\Component\Routing\ChainRouter */
    private $router;

    protected static $routePrefix = '/api/test/v1';

    public function testParse()
    {
        $uri = self::$routePrefix . '/';
        $request = Request::create($uri, 'GET');

        $expectedMatchResult = [
            '_route' => 'ezpublish_rest_testRoute',
            '_controller' => '',
        ];

        $this->getRouterMock()
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->attributeEqualTo('pathInfo', '/api/test/v1/'))
            ->will($this->returnValue($expectedMatchResult));

        self::assertEquals(
            $expectedMatchResult,
            $this->getRequestParser()->parse($uri)
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No route matched '/api/test/v1/nomatch'
     */
    public function testParseNoMatch()
    {
        $uri = self::$routePrefix . '/nomatch';

        $this->getRouterMock()
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->attributeEqualTo('pathInfo', $uri))
            ->will($this->throwException(new ResourceNotFoundException()));

        $this->getRequestParser()->parse($uri);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No route matched '/no/prefix'
     */
    public function testParseNoPrefix()
    {
        $uri = '/no/prefix';

        $this->getRouterMock()
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->attributeEqualTo('pathInfo', $uri))
            ->will($this->throwException(new ResourceNotFoundException()));

        $this->getRequestParser()->parse($uri);
    }

    public function testParseHref()
    {
        $href = '/api/test/v1/content/objects/1';

        $expectedMatchResult = [
            '_route' => 'ezpublish_rest_testParseHref',
            'contentId' => 1,
        ];

        $this->getRouterMock()
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->attributeEqualTo('pathInfo', $href))
            ->will($this->returnValue($expectedMatchResult));

        self::assertEquals(1, $this->getRequestParser()->parseHref($href, 'contentId'));
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage No such attribute 'badAttribute' in route matched from /api/test/v1/content/no-attribute
     */
    public function testParseHrefAttributeNotFound()
    {
        $href = '/api/test/v1/content/no-attribute';

        $matchResult = [
            '_route' => 'ezpublish_rest_testParseHrefAttributeNotFound',
        ];

        $this->getRouterMock()
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->attributeEqualTo('pathInfo', $href))
            ->will($this->returnValue($matchResult));

        self::assertEquals(1, $this->getRequestParser()->parseHref($href, 'badAttribute'));
    }

    public function testGenerate()
    {
        $routeName = 'ezpublish_rest_testGenerate';
        $arguments = ['arg1' => 1];

        $expectedResult = self::$routePrefix . '/generate/' . $arguments['arg1'];
        $this->getRouterMock()
            ->expects($this->once())
            ->method('generate')
            ->with($routeName, $arguments)
            ->will($this->returnValue($expectedResult));

        self::assertEquals(
            $expectedResult,
            $this->getRequestParser()->generate($routeName, $arguments)
        );
    }

    /**
     * @return \eZ\Bundle\EzPublishRestBundle\RequestParser\Router
     */
    private function getRequestParser()
    {
        return new RouterRequestParser(
            $this->getRouterMock()
        );
    }

    /**
     * @return \Symfony\Cmf\Component\Routing\ChainRouter|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRouterMock()
    {
        if (!isset($this->router)) {
            $this->router = $this->createMock(ChainRouter::class);

            $this->router
                ->expects($this->any())
                ->method('getContext')
                ->will($this->returnValue(new RequestContext()));
        }

        return $this->router;
    }
}
