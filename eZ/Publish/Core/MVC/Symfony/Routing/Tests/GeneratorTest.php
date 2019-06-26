<?php

/**
 * File containing the GeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class GeneratorTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator|\PHPUnit\Framework\MockObject\MockObject */
    private $generator;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $siteAccessRouter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->siteAccessRouter = $this->createMock(SiteAccessRouterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->generator = $this->getMockForAbstractClass(Generator::class);
        $this->generator->setSiteAccessRouter($this->siteAccessRouter);
        $this->generator->setLogger($this->logger);
    }

    public function generateProvider()
    {
        return [
            ['foo_bar', [], UrlGeneratorInterface::ABSOLUTE_PATH],
            ['foo_bar', [], UrlGeneratorInterface::ABSOLUTE_URL],
            ['foo_bar', ['some' => 'thing'], UrlGeneratorInterface::ABSOLUTE_URL],
            [new Location(), [], UrlGeneratorInterface::ABSOLUTE_PATH],
            [new Location(), [], UrlGeneratorInterface::ABSOLUTE_URL],
            [new Location(), ['some' => 'thing'], UrlGeneratorInterface::ABSOLUTE_URL],
            [new \stdClass(), [], UrlGeneratorInterface::ABSOLUTE_PATH],
            [new \stdClass(), [], UrlGeneratorInterface::ABSOLUTE_URL],
            [new \stdClass(), ['some' => 'thing'], UrlGeneratorInterface::ABSOLUTE_URL],
        ];
    }

    /**
     * @dataProvider generateProvider
     */
    public function testSimpleGenerate($urlResource, array $parameters, $referenceType)
    {
        $matcher = $this->createMock(URILexer::class);
        $this->generator->setSiteAccess(new SiteAccess('test', 'fake', $matcher));

        $baseUrl = '/base/url';
        $requestContext = new RequestContext($baseUrl);
        $this->generator->setRequestContext($requestContext);

        $uri = '/some/thing';
        $this->generator
            ->expects($this->once())
            ->method('doGenerate')
            ->with($urlResource, $parameters)
            ->will($this->returnValue($uri));

        $fullUri = $baseUrl . $uri;
        $matcher
            ->expects($this->once())
            ->method('analyseLink')
            ->with($uri)
            ->will($this->returnValue($uri));

        if ($referenceType === UrlGeneratorInterface::ABSOLUTE_URL) {
            $fullUri = $requestContext->getScheme() . '://' . $requestContext->getHost() . $baseUrl . $uri;
        }

        $this->assertSame($fullUri, $this->generator->generate($urlResource, $parameters, $referenceType));
    }

    /**
     * @dataProvider generateProvider
     */
    public function testGenerateWithSiteAccessNoReverseMatch($urlResource, array $parameters, $referenceType)
    {
        $matcher = $this->createMock(URILexer::class);
        $this->generator->setSiteAccess(new SiteAccess('test', 'test', $matcher));

        $baseUrl = '/base/url';
        $requestContext = new RequestContext($baseUrl);
        $this->generator->setRequestContext($requestContext);

        $uri = '/some/thing';
        $this->generator
            ->expects($this->once())
            ->method('doGenerate')
            ->with($urlResource, $parameters)
            ->will($this->returnValue($uri));

        $fullUri = $baseUrl . $uri;
        $matcher
            ->expects($this->once())
            ->method('analyseLink')
            ->with($uri)
            ->will($this->returnValue($uri));

        if ($referenceType === UrlGeneratorInterface::ABSOLUTE_URL) {
            $fullUri = $requestContext->getScheme() . '://' . $requestContext->getHost() . $baseUrl . $uri;
        }

        $siteAccessName = 'fake';
        $this->siteAccessRouter
            ->expects($this->once())
            ->method('matchByName')
            ->with($siteAccessName)
            ->will($this->returnValue(null));
        $this->logger
            ->expects($this->once())
            ->method('notice');
        $this->assertSame($fullUri, $this->generator->generate($urlResource, $parameters + ['siteaccess' => $siteAccessName], $referenceType));
    }
}
