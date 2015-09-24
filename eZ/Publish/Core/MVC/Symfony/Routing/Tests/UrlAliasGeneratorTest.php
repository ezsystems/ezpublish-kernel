<?php

/**
 * File containing the UrlAliasGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit_Framework_TestCase;

class UrlAliasGeneratorTest extends PHPUnit_Framework_TestCase
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
                    array('sudo')
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

        $urlAliasCharmap = array(
            '"' => '%22',
            "'" => '%27',
            '<' => '%3C',
            '>' => '%3E',
        );
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
        $rootLocation = new Location(array('id' => $rootLocationId));
        $pathPrefix = '/foo/bar';
        $rootUrlAlias = new URLAlias(array('path' => $pathPrefix));
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
            array(
                '/products',
                '/shared/content',
                '/something/in-the-way/',
            )
        );
        $this->assertSame($expectedIsExcluded, $this->urlAliasGenerator->isUriPrefixExcluded($uri));
    }

    public function providerTestIsPrefixExcluded()
    {
        return array(
            array('/foo/bar', false),
            array('/products/bar', true),
            array('/ProDUctS/eZ-Publish', true),
            array('/ProductsFoo/eZ-Publish', true),
            array('/shared/foo', false),
            array('/SHARED/contenT/bar', true),
            array('/SomeThing/bidule/chose', false),
            array('/SomeThing/in-the-way/truc/', true),
            array('/CMS/eZ-Publish', false),
            array('/Lyon/Best/city', false),
        );
    }

    public function testLoadLocation()
    {
        $locationId = 123;
        $location = new Location(array('id' => $locationId));
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
        $location = new Location(array('id' => 123));
        $this->urlAliasService
            ->expects($this->once())
            ->method('listLocationAliases')
            ->with($location, false)
            ->will($this->returnValue(array($urlAlias)));

        $this->urlAliasGenerator->setSiteAccess(new SiteAccess('test', 'fake', $this->getMock('eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer')));

        $this->assertSame($expected, $this->urlAliasGenerator->doGenerate($location, $parameters));
    }

    public function providerTestDoGenerate()
    {
        return array(
            array(
                new URLAlias(array('path' => '/foo/bar')),
                array(),
                '/foo/bar',
            ),
            array(
                new URLAlias(array('path' => '/foo/bar')),
                array('some' => 'thing'),
                '/foo/bar?some=thing',
            ),
            array(
                new URLAlias(array('path' => '/foo/bar')),
                array('some' => 'thing', 'truc' => 'muche'),
                '/foo/bar?some=thing&truc=muche',
            ),
        );
    }

    /**
     * @dataProvider providerTestDoGenerateWithSiteaccess
     */
    public function testDoGenerateWithSiteAccessParam(URLAlias $urlAlias, array $parameters, $expected)
    {
        $siteaccessName = 'foo';
        $parameters += array('siteaccess' => $siteaccessName);
        $languages = array('esl-ES', 'fre-FR', 'eng-GB');

        $saRootLocations = array(
            'foo' => 2,
            'bar' => 100,
        );
        $treeRootUrlAlias = array(
            2 => new URLAlias(array('path' => '/')),
            100 => new URLAlias(array('path' => '/foo/bar')),
        );

        $this->configResolver
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    array(
                        array('languages', null, 'foo', $languages),
                        array('languages', null, 'bar', $languages),
                        array('content.tree_root.location_id', null, 'foo', $saRootLocations['foo']),
                        array('content.tree_root.location_id', null, 'bar', $saRootLocations['bar']),
                    )
                )
            );

        $location = new Location(array('id' => 123));
        $this->urlAliasService
            ->expects($this->exactly(1))
            ->method('listLocationAliases')
            ->will(
                $this->returnValueMap(
                    array(
                        array($location, false, null, null, $languages, array($urlAlias)),
                    )
                )
            );

        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->will(
                $this->returnCallback(
                    function ($locationId) {
                        return new Location(array('id' => $locationId));
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
        return array(
            array(
                new URLAlias(array('path' => '/foo/bar')),
                array(),
                '/foo/bar',
            ),
            array(
                new URLAlias(array('path' => '/foo/bar/baz')),
                array('siteaccess' => 'bar'),
                '/baz',
            ),
            array(
                new UrlAlias(array('path' => '/special-chars-"<>\'')),
                array(),
                '/special-chars-%22%3C%3E%27',
            ),
        );
    }

    public function testDoGenerateNoUrlAlias()
    {
        $location = new Location(array('id' => 123, 'contentInfo' => new ContentInfo(array('id' => 456))));
        $uri = "/content/location/$location->id";
        $this->urlAliasService
            ->expects($this->once())
            ->method('listLocationAliases')
            ->with($location, false)
            ->will($this->returnValue(array()));
        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with(
                UrlAliasGenerator::INTERNAL_CONTENT_VIEW_ROUTE,
                array('contentId' => $location->contentId, 'locationId' => $location->id)
            )
            ->will($this->returnValue($uri));

        $this->assertSame($uri, $this->urlAliasGenerator->doGenerate($location, array()));
    }

    /**
     * @dataProvider providerTestDoGenerateRootLocation
     */
    public function testDoGenerateRootLocation(URLAlias $urlAlias, $isOutsideAndNotExcluded, $expected, $pathPrefix)
    {
        $excludedPrefixes = array('/products', '/shared');
        $rootLocationId = 456;
        $this->urlAliasGenerator->setRootLocationId($rootLocationId);
        $this->urlAliasGenerator->setExcludedUriPrefixes($excludedPrefixes);
        $location = new Location(array('id' => 123));

        $rootLocation = new Location(array('id' => $rootLocationId));
        $rootUrlAlias = new URLAlias(array('path' => $pathPrefix));
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
            ->will($this->returnValue(array($urlAlias)));

        if ($isOutsideAndNotExcluded) {
            $this->logger
                ->expects($this->once())
                ->method('warning');
        }

        $this->assertSame($expected, $this->urlAliasGenerator->doGenerate($location, array()));
    }

    public function providerTestDoGenerateRootLocation()
    {
        return array(
            array(
                new UrlAlias(array('path' => '/my/root-folder/foo/bar')),
                false,
                '/foo/bar',
                '/my/root-folder',
            ),
            array(
                new UrlAlias(array('path' => '/my/root-folder/something')),
                false,
                '/something',
                '/my/root-folder',
            ),
            array(
                new UrlAlias(array('path' => '/my/root-folder')),
                false,
                '/',
                '/my/root-folder',
            ),
            array(
                new UrlAlias(array('path' => '/foo/bar')),
                false,
                '/foo/bar',
                '/',
            ),
            array(
                new UrlAlias(array('path' => '/something')),
                false,
                '/something',
                '/',
            ),
            array(
                new UrlAlias(array('path' => '/')),
                false,
                '/',
                '/',
            ),
            array(
                new UrlAlias(array('path' => '/outside/tree/foo/bar')),
                true,
                '/outside/tree/foo/bar',
                '/my/root-folder',
            ),
            array(
                new UrlAlias(array('path' => '/products/ez-publish')),
                false,
                '/products/ez-publish',
                '/my/root-folder',
            ),
            array(
                new UrlAlias(array('path' => '/shared/some-content')),
                false,
                '/shared/some-content',
                '/my/root-folder',
            ),
        );
    }
}
