<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterHostRegexTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;
use PHPUnit_Framework_TestCase,
    eZ\Publish\Core\MVC\Symfony\SiteAccess\Router,
    eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex\Host as HostRegexMatcher,
    eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

class RouterHostRegexTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router::__construct
     */
    public function testConstruct()
    {
        return new Router(
            "default_sa",
            array(
                "Regex\\Host" => array(
                    "regex" => "^(\\w+_sa)$",
                ),
                "Map\\URI" => array(
                    "first_sa" => "first_sa",
                    "second_sa" => "second_sa",
                ),
                "Map\\Host" => array(
                    "first_sa" => "first_sa",
                    "first_siteaccess" => "first_sa",
                ),
            ),
            array( 'first_sa', 'second_sa', 'third_sa', 'fourth_sa', 'fifth_sa' )
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
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex::match
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex\Host::__construct
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
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/first_sa" ), "default_sa" ),
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
            array( SimplifiedRequest::fromUrl( "http://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:82/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:83/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:82/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:83/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa/foobar/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://second_sa:82/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://second_sa:83/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://second_sa/foo/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://second_sa:82/foo/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://second_sa:83/foo/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://second_sa/foobar/" ), "second_sa" ),

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
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex\Host::getName
     */
    public function testGetName()
    {
        $matcher = new HostRegexMatcher( array( 'host' => 'foo' ), array() );
        $this->assertSame( 'host:regexp', $matcher->getName() );
    }
}
