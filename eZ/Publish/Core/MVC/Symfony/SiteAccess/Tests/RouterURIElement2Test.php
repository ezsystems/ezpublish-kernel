<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterURIElement2Test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement as URIElementMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;

class RouterURIElement2Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder
     */
    private $matcherBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->matcherBuilder = new MatcherBuilder;
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router::__construct
     */
    public function testConstruct()
    {
        return new Router(
            $this->matcherBuilder,
            $this->getMock( 'Psr\\Log\\LoggerInterface' ),
            "default_sa",
            array(
                "URIElement" => 2,
                "Map\\URI" => array(
                    "first_sa" => "first_sa",
                    "second_sa" => "second_sa",
                ),
                "Map\\Host" => array(
                    "first_sa" => "first_sa",
                    "first_siteaccess" => "first_sa",
                ),
            ),
            array( 'first_sa', 'second_sa', 'third_sa', 'fourth_sa', 'fifth_sa', 'foo_baz', 'test_foo', 'first_sa_foo', 'second_sa_foo' )
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
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::setRequest
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::getURIElements
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
            array( SimplifiedRequest::fromUrl( "http://example.com/first_siteaccess/" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/?first_siteaccess" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/?first_sa" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_salt" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa.foo" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/" ), "test_foo" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/" ), "test_foo" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/first_sa" ), "test_foo" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/default_sa" ), "default_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/" ), "first_sa" ),
            // Double slashes shouldn't be considered as one
            array( SimplifiedRequest::fromUrl( "http://example.com//first_sa//" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com///first_sa///test" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com//first_sa//foo/bar" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/foo//bar/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/foo" ), "first_sa_foo" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://third_siteaccess/first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "https://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:81/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/foo/baz/" ), "foo_baz" ),

            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa?param1=foo" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/foo/" ), "second_sa_foo" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:83/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/second_sa/" ), "second_sa" ),
        );
    }

    /**
     * @param int $level
     * @param string $uri
     * @param string $expectedFixedUpURI
     *
     * @dataProvider analyseProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::analyseURI
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::setRequest
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::getURIElements
     */
    public function testAnalyseURI( $level, $uri, $expectedFixedUpURI )
    {
        $matcher = new URIElementMatcher( $level );
        $matcher->setRequest(
            new SimplifiedRequest( array( 'pathinfo' => $uri ) )
        );
        $this->assertSame( $expectedFixedUpURI, $matcher->analyseURI( $uri ) );
    }

    /**
     * @param int $level
     * @param string $fullUri
     * @param string $linkUri
     *
     * @dataProvider analyseProvider
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::analyseLink
     */
    public function testAnalyseLink( $level, $fullUri, $linkUri )
    {
        $matcher = new URIElementMatcher( $level );
        $matcher->setRequest(
            new SimplifiedRequest( array( 'pathinfo' => $fullUri ) )
        );
        $this->assertSame( $fullUri, $matcher->analyseLink( $linkUri ) );
    }

    public function analyseProvider()
    {
        return array(
            array( 2, '/my/siteaccess/foo/bar', '/foo/bar' ),
            array( 2, '/vive/le/sucre/en-poudre', '/sucre/en-poudre' ),
            // Issue https://jira.ez.no/browse/EZP-20125
            array( 1, '/fre/content/edit/104/1/fre-FR', '/content/edit/104/1/fre-FR' ),
            array( 1, '/fre/utf8-with-accent/é/fre/à/à/fre/é', '/utf8-with-accent/é/fre/à/à/fre/é' ),
            array( 2, '/é/fre/utf8-with-accent/é/fre/à/à/fre/é', '/utf8-with-accent/é/fre/à/à/fre/é' ),
            array( 2, '/prefix/fre/url/alias/prefix/fre/prefix/fre/url', '/url/alias/prefix/fre/prefix/fre/url' ),
            // regression after the first fix of EZP-20125
            array( 1, '/sitaccess', '' ),
            array( 1, '/sitaccess/', '/' ),
            array( 2, '/prefix/siteaccess', '' ),
            array( 2, '/prefix/siteaccess/', '/' ),
        );
    }
}
