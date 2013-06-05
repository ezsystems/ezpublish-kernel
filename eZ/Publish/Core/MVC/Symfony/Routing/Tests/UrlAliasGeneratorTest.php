<?php
/**
 * File containing the UrlAliasGeneratorTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\Repository\Values\Content\Location;

class UrlAliasGeneratorTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        parent::setUp();
        $this->router = $this->getMock( 'Symfony\\Component\\Routing\\RouterInterface' );
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
        $this->repository = $repository = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\Repository' )
            ->disableOriginalConstructor()
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
            function () use ( $repository )
            {
                return $repository;
            },
            $this->router,
            $this->logger
        );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::getRepository
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::loadLocation
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::getPathPrefixByRootLocationId
     */
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
     *
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::setExcludedUriPrefixes
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::isUriPrefixExcluded
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

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::loadLocation
     */
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
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::doGenerate
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::setSiteAccess
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

        $siteAccessMatcher = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\URILexer' );
        $siteAccessMatcher
            ->expects( $this->once() )
            ->method( 'analyseLink' )
            ->with( $urlAlias->path )
            ->will( $this->returnArgument( 0 ) );
        $this->urlAliasGenerator->setSiteAccess( new SiteAccess( 'test', 'fake', $siteAccessMatcher ) );

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

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::doGenerate
     */
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
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::setRootLocationId
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::setExcludedUriPrefixes
     * @covers eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::doGenerate
     *
     * @param URLAlias $urlAlias
     * @param $isOutsideAndNotExcluded
     * @param $expected
     */
    public function testDoGenerateRootLocation( URLAlias $urlAlias, $isOutsideAndNotExcluded, $expected )
    {
        $excludedPrefixes = array( '/products', '/shared' );
        $rootLocationId = 456;
        $this->urlAliasGenerator->setRootLocationId( $rootLocationId );
        $this->urlAliasGenerator->setExcludedUriPrefixes( $excludedPrefixes );
        $location = new Location( array( 'id' => 123 ) );

        $rootLocation = new Location( array( 'id' => $rootLocationId ) );
        $pathPrefix = '/my/root-folder';
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
                '/foo/bar'
            ),
            array(
                new UrlAlias( array( 'path' => '/my/root-folder/something' ) ),
                false,
                '/something'
            ),
            array(
                new UrlAlias( array( 'path' => '/my/root-folder' ) ),
                false,
                '/'
            ),
            array(
                new UrlAlias( array( 'path' => '/outside/tree/foo/bar' ) ),
                true,
                '/outside/tree/foo/bar'
            ),
            array(
                new UrlAlias( array( 'path' => '/products/ez-publish' ) ),
                false,
                '/products/ez-publish'
            ),
            array(
                new UrlAlias( array( 'path' => '/shared/some-content' ) ),
                false,
                '/shared/some-content'
            ),
        );
    }
}
