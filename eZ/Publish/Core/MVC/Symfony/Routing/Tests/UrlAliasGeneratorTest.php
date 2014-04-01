<?php
/**
 * File containing the UrlAliasGeneratorTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

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

    protected function setUp()
    {
        parent::setUp();
        $this->router = $this->getMock( 'Symfony\\Component\\Routing\\RouterInterface' );
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
        $this->siteAccessRouter = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface' );
        $repositoryClass = 'eZ\\Publish\\Core\\Repository\\Repository';
        $this->repository = $repository = $this
            ->getMockBuilder( $repositoryClass )
            ->disableOriginalConstructor()
            ->setMethods(
                array_diff(
                    get_class_methods( $repositoryClass ),
                    array( 'sudo' )
                )
            )
            ->getMock();
        $this->urlAliasService = $this->getMock( 'eZ\\Publish\\API\\Repository\\URLAliasService' );
        $this->locationService = $this->getMock( 'eZ\\Publish\\API\\Repository\\LocationService' );
        $this->repository
            ->expects( $this->any() )
            ->method( 'getUrlAliasService' )
            ->will( $this->returnValue( $this->urlAliasService ) );
        $this->repository
            ->expects( $this->any() )
            ->method( 'getLocationService' )
            ->will( $this->returnValue( $this->locationService ) );

        $this->urlAliasGenerator = new UrlAliasGenerator(
            $this->repository,
            $this->router
        );
        $this->urlAliasGenerator->setLogger( $this->logger );
        $this->urlAliasGenerator->setSiteAccessRouter( $this->siteAccessRouter );
    }

    public function testGetPathPrefixByRootLocationId()
    {
        $rootLocationId = 123;
        $rootLocation = new Location( array( 'id' => $rootLocationId ) );
        $pathPrefix = '/foo/bar';
        $rootUrlAlias = new URLAlias( array( 'path' => $pathPrefix ) );
        $this->locationService
            ->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $rootLocationId )
            ->will( $this->returnValue( $rootLocation ) );
        $this->urlAliasService
            ->expects( $this->once() )
            ->method( 'reverseLookup' )
            ->with( $rootLocation )
            ->will( $this->returnValue( $rootUrlAlias ) );

        $this->assertSame( $pathPrefix, $this->urlAliasGenerator->getPathPrefixByRootLocationId( $rootLocationId ) );
    }

    /**
     * @dataProvider providerTestIsPrefixExcluded
     */
    public function testIsPrefixExcluded( $uri, $expectedIsExcluded )
    {
        $this->urlAliasGenerator->setExcludedUriPrefixes(
            array(
                '/products',
                '/shared/content',
                '/something/in-the-way/',
            )
        );
        $this->assertSame( $expectedIsExcluded, $this->urlAliasGenerator->isUriPrefixExcluded( $uri ) );
    }

    public function providerTestIsPrefixExcluded()
    {
        return array(
            array( '/foo/bar', false ),
            array( '/products/bar', true ),
            array( '/ProDUctS/eZ-Publish', true ),
            array( '/ProductsFoo/eZ-Publish', true ),
            array( '/shared/foo', false ),
            array( '/SHARED/contenT/bar', true ),
            array( '/SomeThing/bidule/chose', false ),
            array( '/SomeThing/in-the-way/truc/', true ),
            array( '/CMS/eZ-Publish', false ),
            array( '/Lyon/Best/city', false ),
        );
    }

    public function testLoadLocation()
    {
        $locationId = 123;
        $location = new Location( array( 'id' => $locationId ) );
        $this->locationService
            ->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $locationId )
            ->will( $this->returnValue( $location ) );
        $this->urlAliasGenerator->loadLocation( $locationId );
    }

    /**
     * @dataProvider providerTestDoGenerate
     *
     * @param \eZ\Publish\API\Repository\Values\Content\URLAlias $urlAlias
     * @param array $parameters
     * @param $expected
     */
    public function testDoGenerate( URLAlias $urlAlias, array $parameters, $expected )
    {
        $location = new Location( array( 'id' => 123 ) );
        $this->urlAliasService
            ->expects( $this->once() )
            ->method( 'listLocationAliases' )
            ->with( $location, false )
            ->will( $this->returnValue( array( $urlAlias ) ) );

        $this->urlAliasGenerator->setSiteAccess( new SiteAccess( 'test', 'fake', $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer' ) ) );

        $this->assertSame( $expected, $this->urlAliasGenerator->doGenerate( $location, $parameters ) );
    }

    public function providerTestDoGenerate()
    {
        return array(
            array(
                new URLAlias( array( 'path' => '/foo/bar' ) ),
                array(),
                '/foo/bar'
            ),
            array(
                new URLAlias( array( 'path' => '/foo/bar' ) ),
                array( 'some' => 'thing' ),
                '/foo/bar?some=thing'
            ),
            array(
                new URLAlias( array( 'path' => '/foo/bar' ) ),
                array( 'some' => 'thing', 'truc' => 'muche' ),
                '/foo/bar?some=thing&truc=muche'
            ),
        );
    }

    public function testDoGenerateNoUrlAlias()
    {
        $location = new Location( array( 'id' => 123 ) );
        $uri = "/content/location/$location->id";
        $this->urlAliasService
            ->expects( $this->once() )
            ->method( 'listLocationAliases' )
            ->with( $location, false )
            ->will( $this->returnValue( array() ) );
        $this->router
            ->expects( $this->once() )
            ->method( 'generate' )
            ->with(
                UrlAliasGenerator::INTERNAL_LOCATION_ROUTE,
                array( 'locationId' => $location->id )
            )
            ->will( $this->returnValue( $uri ) );

        $this->assertSame( $uri, $this->urlAliasGenerator->doGenerate( $location, array() ) );
    }

    /**
     * @dataProvider providerTestDoGenerateRootLocation
     *
     * @param URLAlias $urlAlias
     * @param $isOutsideAndNotExcluded
     * @param $expected
     * @param $pathPrefix
     */
    public function testDoGenerateRootLocation( URLAlias $urlAlias, $isOutsideAndNotExcluded, $expected, $pathPrefix )
    {
        $excludedPrefixes = array( '/products', '/shared' );
        $rootLocationId = 456;
        $this->urlAliasGenerator->setRootLocationId( $rootLocationId );
        $this->urlAliasGenerator->setExcludedUriPrefixes( $excludedPrefixes );
        $location = new Location( array( 'id' => 123 ) );

        $rootLocation = new Location( array( 'id' => $rootLocationId ) );
        $rootUrlAlias = new URLAlias( array( 'path' => $pathPrefix ) );
        $this->locationService
            ->expects( $this->once() )
            ->method( 'loadLocation' )
            ->with( $rootLocationId )
            ->will( $this->returnValue( $rootLocation ) );
        $this->urlAliasService
            ->expects( $this->once() )
            ->method( 'reverseLookup' )
            ->with( $rootLocation )
            ->will( $this->returnValue( $rootUrlAlias ) );

        $this->urlAliasService
            ->expects( $this->once() )
            ->method( 'listLocationAliases' )
            ->with( $location, false )
            ->will( $this->returnValue( array( $urlAlias ) ) );

        if ( $isOutsideAndNotExcluded )
        {
            $this->logger
                ->expects( $this->once() )
                ->method( 'warning' );
        }

        $this->assertSame( $expected, $this->urlAliasGenerator->doGenerate( $location, array() ) );
    }

    public function providerTestDoGenerateRootLocation()
    {
        return array(
            array(
                new UrlAlias( array( 'path' => '/my/root-folder/foo/bar' ) ),
                false,
                '/foo/bar',
                '/my/root-folder'
            ),
            array(
                new UrlAlias( array( 'path' => '/my/root-folder/something' ) ),
                false,
                '/something',
                '/my/root-folder'
            ),
            array(
                new UrlAlias( array( 'path' => '/my/root-folder' ) ),
                false,
                '/',
                '/my/root-folder'
            ),
            array(
                new UrlAlias( array( 'path' => '/foo/bar' ) ),
                false,
                '/foo/bar',
                '/'
            ),
            array(
                new UrlAlias( array( 'path' => '/something' ) ),
                false,
                '/something',
                '/'
            ),
            array(
                new UrlAlias( array( 'path' => '/' ) ),
                false,
                '/',
                '/'
            ),
            array(
                new UrlAlias( array( 'path' => '/outside/tree/foo/bar' ) ),
                true,
                '/outside/tree/foo/bar',
                '/my/root-folder'
            ),
            array(
                new UrlAlias( array( 'path' => '/products/ez-publish' ) ),
                false,
                '/products/ez-publish',
                '/my/root-folder'
            ),
            array(
                new UrlAlias( array( 'path' => '/shared/some-content' ) ),
                false,
                '/shared/some-content',
                '/my/root-folder'
            ),
        );
    }
}
