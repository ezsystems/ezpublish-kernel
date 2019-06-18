<?php

/**
 * File containing the UrlAliasGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

class UrlAliasGeneratorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlAliasService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var UrlAliasGenerator
     */
    private $urlAliasGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $siteAccessRouter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->router = $this->getMock('Symfony\\Component\\Routing\\RouterInterface');
        $this->logger = $this->getMock('Psr\\Log\\LoggerInterface');
        $this->siteAccessRouter = $this->getMock('eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface');
        $this->configResolver = $this->getMock('eZ\Publish\Core\MVC\ConfigResolverInterface');
        $repositoryClass = 'eZ\\Publish\\Core\\Repository\\Repository';
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
        $this->urlAliasService = $this->getMock('eZ\\Publish\\API\\Repository\\URLAliasService');
        $this->locationService = $this->getMock('eZ\\Publish\\API\\Repository\\LocationService');
        $this->repository
            ->expects($this->any())
            ->method('getURLAliasService')
            ->will($this->returnValue($this->urlAliasService));
        $this->repository
            ->expects($this->any())
            ->method('getLocationService')
            ->will($this->returnValue($this->locationService));
        $repository
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));

        $urlAliasCharmap = [
            '"' => '%22',
            "'" => '%27',
            '<' => '%3C',
            '>' => '%3E',
        ];
        $this->urlAliasGenerator = new UrlAliasGenerator(
            $this->repository,
            $this->router,
            $this->configResolver,
            $urlAliasCharmap
        );
        $this->urlAliasGenerator->setLogger($this->logger);
        $this->urlAliasGenerator->setSiteAccessRouter($this->siteAccessRouter);
    }

    public function testGetPathPrefixByRootLocationId()
    {
        $rootLocationId = 123;
        $rootLocation = new Location(['id' => $rootLocationId]);
        $pathPrefix = '/foo/bar';
        $rootUrlAlias = new URLAlias(['path' => $pathPrefix]);
        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with($rootLocationId)
            ->will($this->returnValue($rootLocation));
        $this->urlAliasService
            ->expects($this->once())
            ->method('reverseLookup')
            ->with($rootLocation)
            ->will($this->returnValue($rootUrlAlias));

        $this->assertSame($pathPrefix, $this->urlAliasGenerator->getPathPrefixByRootLocationId($rootLocationId));
    }

    /**
     * @dataProvider providerTestIsPrefixExcluded
     */
    public function testIsPrefixExcluded($uri, $expectedIsExcluded)
    {
        $this->urlAliasGenerator->setExcludedUriPrefixes(
            [
                '/products',
                '/shared/content',
                '/something/in-the-way/',
            ]
        );
        $this->assertSame($expectedIsExcluded, $this->urlAliasGenerator->isUriPrefixExcluded($uri));
    }

    public function providerTestIsPrefixExcluded()
    {
        return [
            ['/foo/bar', false],
            ['/products/bar', true],
            ['/ProDUctS/eZ-Publish', true],
            ['/ProductsFoo/eZ-Publish', true],
            ['/shared/foo', false],
            ['/SHARED/contenT/bar', true],
            ['/SomeThing/bidule/chose', false],
            ['/SomeThing/in-the-way/truc/', true],
            ['/CMS/eZ-Publish', false],
            ['/Lyon/Best/city', false],
        ];
    }

    public function testLoadLocation()
    {
        $locationId = 123;
        $location = new Location(['id' => $locationId]);
        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with($locationId)
            ->will($this->returnValue($location));
        $this->urlAliasGenerator->loadLocation($locationId);
    }

    /**
     * @dataProvider providerTestDoGenerate
     */
    public function testDoGenerate(URLAlias $urlAlias, array $parameters, $expected)
    {
        $location = new Location(['id' => 123]);
        $this->urlAliasService
            ->expects($this->once())
            ->method('listLocationAliases')
            ->with($location, false)
            ->will($this->returnValue([$urlAlias]));

        $this->urlAliasGenerator->setSiteAccess(new SiteAccess('test', 'fake', $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer')));

        $this->assertSame($expected, $this->urlAliasGenerator->doGenerate($location, $parameters));
    }

    public function providerTestDoGenerate()
    {
        return [
            [
                new URLAlias(['path' => '/foo/bar']),
                [],
                '/foo/bar',
            ],
            [
                new URLAlias(['path' => '/foo/bar']),
                ['some' => 'thing'],
                '/foo/bar?some=thing',
            ],
            [
                new URLAlias(['path' => '/foo/bar']),
                ['some' => 'thing', 'truc' => 'muche'],
                '/foo/bar?some=thing&truc=muche',
            ],
        ];
    }

    /**
     * @dataProvider providerTestDoGenerateWithSiteaccess
     */
    public function testDoGenerateWithSiteAccessParam(URLAlias $urlAlias, array $parameters, $expected)
    {
        $siteaccessName = 'foo';
        $parameters += ['siteaccess' => $siteaccessName];
        $languages = ['esl-ES', 'fre-FR', 'eng-GB'];

        $saRootLocations = [
            'foo' => 2,
            'bar' => 100,
        ];
        $treeRootUrlAlias = [
            2 => new URLAlias(['path' => '/']),
            100 => new URLAlias(['path' => '/foo/bar']),
        ];

        $this->configResolver
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        ['languages', null, 'foo', $languages],
                        ['languages', null, 'bar', $languages],
                        ['content.tree_root.location_id', null, 'foo', $saRootLocations['foo']],
                        ['content.tree_root.location_id', null, 'bar', $saRootLocations['bar']],
                    ]
                )
            );

        $location = new Location(['id' => 123]);
        $this->urlAliasService
            ->expects($this->exactly(1))
            ->method('listLocationAliases')
            ->will(
                $this->returnValueMap(
                    [
                        [$location, false, null, null, $languages, [$urlAlias]],
                    ]
                )
            );

        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->will(
                $this->returnCallback(
                    function ($locationId) {
                        return new Location(['id' => $locationId]);
                    }
                )
            );
        $this->urlAliasService
            ->expects($this->exactly(1))
            ->method('reverseLookup')
            ->will(
                $this->returnCallback(
                    function ($location) use ($treeRootUrlAlias) {
                        return $treeRootUrlAlias[$location->id];
                    }
                )
            );

        $this->urlAliasGenerator->setSiteAccess(new SiteAccess('test', 'fake', $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer')));

        $this->assertSame($expected, $this->urlAliasGenerator->doGenerate($location, $parameters));
    }

    public function providerTestDoGenerateWithSiteaccess()
    {
        return [
            [
                new URLAlias(['path' => '/foo/bar']),
                [],
                '/foo/bar',
            ],
            [
                new URLAlias(['path' => '/foo/bar/baz']),
                ['siteaccess' => 'bar'],
                '/baz',
            ],
            [
                new UrlAlias(['path' => '/special-chars-"<>\'']),
                [],
                '/special-chars-%22%3C%3E%27',
            ],
        ];
    }

    public function testDoGenerateNoUrlAlias()
    {
        $location = new Location(['id' => 123, 'contentInfo' => new ContentInfo(['id' => 456])]);
        $uri = "/content/location/$location->id";
        $this->urlAliasService
            ->expects($this->once())
            ->method('listLocationAliases')
            ->with($location, false)
            ->will($this->returnValue([]));
        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with(
                UrlAliasGenerator::INTERNAL_CONTENT_VIEW_ROUTE,
                ['contentId' => $location->contentId, 'locationId' => $location->id]
            )
            ->will($this->returnValue($uri));

        $this->assertSame($uri, $this->urlAliasGenerator->doGenerate($location, []));
    }

    /**
     * @dataProvider providerTestDoGenerateRootLocation
     */
    public function testDoGenerateRootLocation(URLAlias $urlAlias, $isOutsideAndNotExcluded, $expected, $pathPrefix)
    {
        $excludedPrefixes = ['/products', '/shared'];
        $rootLocationId = 456;
        $this->urlAliasGenerator->setRootLocationId($rootLocationId);
        $this->urlAliasGenerator->setExcludedUriPrefixes($excludedPrefixes);
        $location = new Location(['id' => 123]);

        $rootLocation = new Location(['id' => $rootLocationId]);
        $rootUrlAlias = new URLAlias(['path' => $pathPrefix]);
        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with($rootLocationId)
            ->will($this->returnValue($rootLocation));
        $this->urlAliasService
            ->expects($this->once())
            ->method('reverseLookup')
            ->with($rootLocation)
            ->will($this->returnValue($rootUrlAlias));

        $this->urlAliasService
            ->expects($this->once())
            ->method('listLocationAliases')
            ->with($location, false)
            ->will($this->returnValue([$urlAlias]));

        if ($isOutsideAndNotExcluded) {
            $this->logger
                ->expects($this->once())
                ->method('warning');
        }

        $this->assertSame($expected, $this->urlAliasGenerator->doGenerate($location, []));
    }

    public function providerTestDoGenerateRootLocation()
    {
        return [
            [
                new UrlAlias(['path' => '/my/root-folder/foo/bar']),
                false,
                '/foo/bar',
                '/my/root-folder',
            ],
            [
                new UrlAlias(['path' => '/my/root-folder/something']),
                false,
                '/something',
                '/my/root-folder',
            ],
            [
                new UrlAlias(['path' => '/my/root-folder']),
                false,
                '/',
                '/my/root-folder',
            ],
            [
                new UrlAlias(['path' => '/foo/bar']),
                false,
                '/foo/bar',
                '/',
            ],
            [
                new UrlAlias(['path' => '/something']),
                false,
                '/something',
                '/',
            ],
            [
                new UrlAlias(['path' => '/']),
                false,
                '/',
                '/',
            ],
            [
                new UrlAlias(['path' => '/outside/tree/foo/bar']),
                true,
                '/outside/tree/foo/bar',
                '/my/root-folder',
            ],
            [
                new UrlAlias(['path' => '/products/ez-publish']),
                false,
                '/products/ez-publish',
                '/my/root-folder',
            ],
            [
                new UrlAlias(['path' => '/shared/some-content']),
                false,
                '/shared/some-content',
                '/my/root-folder',
            ],
            [
                new UrlAlias(['path' => '/products/ez-publish']),
                false,
                '/products/ez-publish',
                '/prod',
            ],
        ];
    }

    protected function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder('\eZ\Publish\Core\Repository\Permission\PermissionResolver')
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\RoleDomainMapper')
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\LimitationService')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\SPI\Persistence\User\Handler')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\API\Repository\Values\User\UserReference')
                        ->getMock(),
                ]
            )
            ->getMock();
    }
}
