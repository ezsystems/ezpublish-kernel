<?php

/**
 * File containing the UrlAliasRouterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class UrlAliasRouterTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $repository;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $urlAliasService;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $locationService;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $contentService;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $urlALiasGenerator;

    protected $requestContext;

    /** @var UrlAliasRouter */
    protected $router;

    protected function setUp()
    {
        parent::setUp();
        $repositoryClass = Repository::class;
        $this->repository = $repository = $this
            ->getMockBuilder($repositoryClass)
            ->disableOriginalConstructor()
            ->setMethods(
                array_diff(
                    get_class_methods($repositoryClass),
                    ['sudo']
                )
            )
            ->getMock();
        $this->urlAliasService = $this->createMock(URLAliasService::class);
        $this->locationService = $this->createMock(LocationService::class);
        $this->contentService = $this->createMock(ContentService::class);
        $this->urlALiasGenerator = $this
            ->getMockBuilder(UrlAliasGenerator::class)
            ->setConstructorArgs(
                [
                    $repository,
                    $this->createMock(RouterInterface::class),
                    $this->createMock(ConfigResolverInterface::class),
                ]
            )
            ->getMock();
        $this->requestContext = new RequestContext();

        $this->router = $this->getRouter($this->locationService, $this->urlAliasService, $this->contentService, $this->urlALiasGenerator, $this->requestContext);
    }

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param UrlAliasGenerator $urlAliasGenerator
     * @param RequestContext $requestContext
     *
     * @return UrlAliasRouter
     */
    protected function getRouter(LocationService $locationService, URLAliasService $urlAliasService, ContentService $contentService, UrlAliasGenerator $urlAliasGenerator, RequestContext $requestContext)
    {
        return new UrlAliasRouter($locationService, $urlAliasService, $contentService, $urlAliasGenerator, $requestContext);
    }

    public function testRequestContext()
    {
        $this->assertSame($this->requestContext, $this->router->getContext());
        $newContext = new RequestContext();
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('setRequestContext')
            ->with($newContext);
        $this->router->setContext($newContext);
        $this->assertSame($newContext, $this->router->getContext());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMatch()
    {
        $this->router->match('/foo');
    }

    /**
     * @dataProvider providerTestSupports
     */
    public function testSupports($routeReference, $isSupported)
    {
        $this->assertSame($isSupported, $this->router->supports($routeReference));
    }

    public function providerTestSupports()
    {
        return [
            [new Location(), true],
            [new \stdClass(), false],
            [UrlAliasRouter::URL_ALIAS_ROUTE_NAME, true],
            ['some_route_name', false],
        ];
    }

    public function testGetRouteCollection()
    {
        $this->assertInstanceOf(RouteCollection::class, $this->router->getRouteCollection());
    }

    /**
     * @param $pathInfo
     *
     * @return Request
     */
    protected function getRequestByPathInfo($pathInfo)
    {
        $request = Request::create($pathInfo);
        $request->attributes->set('semanticPathinfo', $pathInfo);
        $request->attributes->set(
            'siteaccess',
            new SiteAccess(
                'test',
                'fake',
                $this->createMock(Matcher::class)
            )
        );

        return $request;
    }

    public function testMatchRequestLocation()
    {
        $pathInfo = '/foo/bar';
        $destinationId = 123;
        $urlAlias = new URLAlias(
            [
                'path' => $pathInfo,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => false,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->will($this->returnValue(new Location(['contentInfo' => new ContentInfo(['id' => 456])])));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            'locationId' => $destinationId,
            'contentId' => 456,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestLocationWithCaseRedirect()
    {
        $pathInfo = '/Foo/bAR';
        $urlAliasPath = '/foo/bar';
        $destinationId = 123;
        $urlAlias = new URLAlias(
            [
                'path' => $urlAliasPath,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => false,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('isUriPrefixExcluded')
            ->with($pathInfo)
            ->will($this->returnValue(false));
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->will($this->returnValue(new Location(['contentInfo' => new ContentInfo(['id' => 456])])));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            'locationId' => $destinationId,
            'contentId' => 456,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true,
            'needsRedirect' => true,
            'semanticPathinfo' => $urlAliasPath,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestLocationWrongCaseUriPrefixExcluded()
    {
        $pathInfo = '/Foo/bAR';
        $urlAliasPath = '/foo/bar';
        $destinationId = 123;
        $urlAlias = new URLAlias(
            [
                'path' => $urlAliasPath,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => false,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('isUriPrefixExcluded')
            ->with($pathInfo)
            ->will($this->returnValue(true));
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->will($this->returnValue(new Location(['contentInfo' => new ContentInfo(['id' => 456])])));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            'contentId' => 456,
            'locationId' => $destinationId,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true,
            'needsRedirect' => true,
            'semanticPathinfo' => $urlAliasPath,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestLocationCorrectCaseUriPrefixExcluded()
    {
        $pathInfo = $urlAliasPath = '/foo/bar';
        $destinationId = 123;
        $urlAlias = new URLAlias(
            [
                'path' => $urlAliasPath,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => false,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('isUriPrefixExcluded')
            ->with($pathInfo)
            ->will($this->returnValue(true));
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->will($this->returnValue(new Location(['contentInfo' => new ContentInfo(['id' => 456])])));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            'locationId' => $destinationId,
            'contentId' => 456,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
        $this->assertFalse($request->attributes->has('needsRedirect'));
        $this->assertSame($pathInfo, $request->attributes->get('semanticPathinfo'));
    }

    public function testMatchRequestLocationHistory()
    {
        $pathInfo = '/foo/bar';
        $newPathInfo = '/foo/bar-new';
        $destinationId = 123;
        $destinationLocation = new Location([
            'id' => $destinationId,
            'contentInfo' => new ContentInfo(['id' => 456]),
        ]);
        $urlAlias = new URLAlias(
            [
                'path' => $pathInfo,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => true,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($destinationLocation)
            ->will($this->returnValue($newPathInfo));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->will($this->returnValue($destinationLocation));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            'locationId' => $destinationId,
            'contentId' => 456,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true,
            'needsRedirect' => true,
            'semanticPathinfo' => $newPathInfo,
            'prependSiteaccessOnRedirect' => false,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestLocationCustom()
    {
        $pathInfo = '/foo/bar';
        $destinationId = 123;
        $urlAlias = new URLAlias(
            [
                'path' => $pathInfo,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => false,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->will($this->returnValue(new Location(['contentInfo' => new ContentInfo(['id' => 456])])));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            'locationId' => $destinationId,
            'contentId' => 456,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestLocationCustomForward()
    {
        $pathInfo = '/foo/bar';
        $newPathInfo = '/foo/bar-new';
        $destinationId = 123;
        $destinationLocation = new Location([
            'id' => $destinationId,
            'contentInfo' => new ContentInfo(['id' => 456]),
        ]);
        $urlAlias = new URLAlias(
            [
                'path' => $pathInfo,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => false,
                'isCustom' => true,
                'forward' => true,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->with($destinationId)
            ->will(
                $this->returnValue($destinationLocation)
            );
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($destinationLocation)
            ->will($this->returnValue($newPathInfo));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('loadLocation')
            ->will($this->returnValue($destinationLocation));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            'locationId' => $destinationId,
            'contentId' => 456,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true,
            'needsRedirect' => true,
            'semanticPathinfo' => $newPathInfo,
            'prependSiteaccessOnRedirect' => false,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchRequestFail()
    {
        $pathInfo = '/foo/bar';
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->throwException(new NotFoundException('URLAlias', $pathInfo)));
        $this->router->matchRequest($request);
    }

    public function testMatchRequestResource()
    {
        $pathInfo = '/hello_content/hello_search';
        $destination = '/content/search';
        $urlAlias = new URLAlias(
            [
                'destination' => $destination,
                'path' => $pathInfo,
                'type' => UrlAlias::RESOURCE,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            'semanticPathinfo' => $destination,
            'needsForward' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestResourceWithRedirect()
    {
        $pathInfo = '/hello_content/hello_search';
        $destination = '/content/search';
        $urlAlias = new URLAlias(
            [
                'destination' => $destination,
                'path' => $pathInfo,
                'type' => UrlAlias::RESOURCE,
                'forward' => true,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            'needsRedirect' => true,
            'semanticPathinfo' => $destination,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestResourceWithCaseRedirect()
    {
        $pathInfo = '/heLLo_contEnt/hEllo_SEarch';
        $urlAliasPath = '/hello_content/hello_search';
        $destination = '/content/search';
        $urlAlias = new URLAlias(
            [
                'destination' => $destination,
                'path' => $urlAliasPath,
                'type' => UrlAlias::RESOURCE,
                'forward' => false,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('isUriPrefixExcluded')
            ->with($pathInfo)
            ->will($this->returnValue(false));
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            'semanticPathinfo' => $urlAliasPath,
            'needsRedirect' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    /**
     * Tests that forwarding custom alias will redirect to the resource destination rather than
     * to the case-corrected alias.
     */
    public function testMatchRequestResourceCaseIncorrectWithForwardRedirect()
    {
        $pathInfo = '/heLLo_contEnt/hEllo_SEarch';
        $urlAliasPath = '/hello_content/hello_search';
        $destination = '/content/search';
        $urlAlias = new URLAlias(
            [
                'destination' => $destination,
                'path' => $urlAliasPath,
                'type' => UrlAlias::RESOURCE,
                'forward' => true,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            'semanticPathinfo' => $destination,
            'needsRedirect' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestVirtual()
    {
        $pathInfo = '/foo/bar';
        $urlAlias = new URLAlias(
            [
                'path' => $pathInfo,
                'type' => UrlAlias::VIRTUAL,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            'semanticPathinfo' => '/',
            'needsForward' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    public function testMatchRequestVirtualWithCaseRedirect()
    {
        $pathInfo = '/Foo/bAR';
        $urlAliasPath = '/foo/bar';
        $urlAlias = new URLAlias(
            [
                'path' => $urlAliasPath,
                'type' => UrlAlias::VIRTUAL,
            ]
        );
        $request = $this->getRequestByPathInfo($pathInfo);
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('isUriPrefixExcluded')
            ->with($pathInfo)
            ->will($this->returnValue(false));
        $this->urlAliasService
            ->expects($this->once())
            ->method('lookup')
            ->with($pathInfo)
            ->will($this->returnValue($urlAlias));

        $expected = [
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            'semanticPathinfo' => $urlAliasPath,
            'needsRedirect' => true,
        ];
        $this->assertEquals($expected, $this->router->matchRequest($request));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateFail()
    {
        $this->router->generate('invalidRoute');
    }

    public function testGenerateWithLocation()
    {
        $location = new Location();
        $parameters = ['some' => 'thing'];
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        $generatedLink = '/foo/bar';
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($location, $parameters, $referenceType)
            ->will($this->returnValue($generatedLink));
        $this->assertSame($generatedLink, $this->router->generate($location, $parameters, $referenceType));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGenerateNoLocation()
    {
        $this->router->generate(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, ['foo' => 'bar']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testGenerateInvalidLocation()
    {
        $this->router->generate(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, ['location' => new \stdClass()]);
    }

    public function testGenerateWithLocationId()
    {
        $locationId = 123;
        $location = new Location(['id' => $locationId]);
        $parameters = ['some' => 'thing'];
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        $generatedLink = '/foo/bar';
        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with($locationId)
            ->will($this->returnValue($location));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($location, $parameters, $referenceType)
            ->will($this->returnValue($generatedLink));
        $this->assertSame(
            $generatedLink,
            $this->router->generate(
                UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
                $parameters + ['locationId' => $locationId],
                $referenceType
            )
        );
    }

    public function testGenerateWithLocationAsParameter()
    {
        $locationId = 123;
        $location = new Location(['id' => $locationId]);
        $parameters = ['some' => 'thing'];
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        $generatedLink = '/foo/bar';
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($location, $parameters, $referenceType)
            ->will($this->returnValue($generatedLink));
        $this->assertSame(
            $generatedLink,
            $this->router->generate(
                UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
                $parameters + ['location' => $location],
                $referenceType
            )
        );
    }

    public function testGenerateWithContentId()
    {
        $locationId = 123;
        $contentId = 456;
        $location = new Location(['id' => $locationId]);
        $contentInfo = new ContentInfo(['id' => $contentId, 'mainLocationId' => $locationId]);
        $parameters = ['some' => 'thing'];
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        $generatedLink = '/foo/bar';
        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId)
            ->will($this->returnValue($contentInfo));
        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with($contentInfo->mainLocationId)
            ->will($this->returnValue($location));
        $this->urlALiasGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($location, $parameters, $referenceType)
            ->will($this->returnValue($generatedLink));
        $this->assertSame(
            $generatedLink,
            $this->router->generate(
                UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
                $parameters + ['contentId' => $contentId],
                $referenceType
            )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testGenerateWithContentIdWithMissingMainLocation()
    {
        $contentId = 456;
        $contentInfo = new ContentInfo(['id' => $contentId, 'mainLocationId' => null]);
        $parameters = ['some' => 'thing'];
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId)
            ->will($this->returnValue($contentInfo));

        $this->router->generate(
            UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            $parameters + ['contentId' => $contentId],
            $referenceType
        );
    }
}
