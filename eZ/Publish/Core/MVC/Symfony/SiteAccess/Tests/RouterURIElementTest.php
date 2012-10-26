<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterURIElementTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;
use PHPUnit_Framework_TestCase,
    eZ\Publish\Core\MVC\Symfony\SiteAccess\Router,
    eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement as URIElementMatcher,
    eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

class RouterURIElementTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router::__construct
     */
    public function testConstruct()
    {
        return new Router(
            "default_sa",
            array(
                "URIElement" => 1,
                "Map\\URI" => array(
                    "first_sa" => "first_sa",
                    "second_sa" => "second_sa",
                ),
                "Map\\Host" => array(
                    "first_sa" => "first_sa",
                    "first_siteaccess" => "first_sa",
                ),
            ),
            array( 'first_sa', 'second_sa', 'first_siteaccess', 'first_salt', 'first_sa.foo', 'test', 'foo' )
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider matchProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router::match
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map::match
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\URI::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Host::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::match
     */
    public function testMatch( $request, $siteAccess, $router )
    {
        $sa = $router->match( $request );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess', $sa );
        $this->assertSame( $siteAccess, $sa->name );
        $router->setSiteAccess();
    }

    public function matchProvider()
    {
        return array(
            array( SimplifiedRequest::fromUrl( "http://example.com" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com/" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com//" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com//" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:8080/" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_siteaccess/" ), "first_siteaccess" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/?first_siteaccess" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/?first_sa" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_salt" ), "first_salt" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa.foo" ), "first_sa.foo" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test" ), "test" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/" ), "test" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/" ), "test" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/first_sa" ), "test" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/default_sa" ), "default_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/" ), "first_sa" ),
            // Double slashes shouldn't be considered as one
            array( SimplifiedRequest::fromUrl( "http://example.com//first_sa//" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com///first_sa///test" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com//first_sa//foo/bar" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/foo" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://third_siteaccess/first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "https://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:81/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/foo/" ), "foo" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/foo/" ), "foo" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/foo/" ), "foo" ),

            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa?param1=foo" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/foo/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:83/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/second_sa/" ), "second_sa" ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::getName
     */
    public function testGetName()
    {
        $matcher = new URIElementMatcher( array(), array() );
        $this->assertSame( 'uri:element', $matcher->getName() );
    }

    /**
     * @param $uri
     * @param $expectedFixedUpURI
     * @dataProvider analyseProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::analyseURI
     */
    public function testAnalyseURI( $uri, $expectedFixedUpURI )
    {
        $matcher = new URIElementMatcher( 1 );
        $matcher->setRequest(
            new SimplifiedRequest( array( 'pathinfo' => $uri ) )
        );
        $this->assertSame( $expectedFixedUpURI, $matcher->analyseURI( $uri ) );
    }

    /**
     * @param $fullUri
     * @param $linkUri
     * @dataProvider analyseProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::analyseLink
     */
    public function testAnalyseLink( $fullUri, $linkUri )
    {
        $matcher = new URIElementMatcher( 1 );
        $matcher->setRequest(
            new SimplifiedRequest( array( 'pathinfo' => $fullUri ) )
        );
        $this->assertSame( $fullUri, $matcher->analyseLink( $linkUri ) );
    }

    public function analyseProvider()
    {
        return array(
            array( '/my_siteaccess/foo/bar', '/foo/bar' ),
            array( '/vive/le/sucre', '/le/sucre' )
        );
    }
}
