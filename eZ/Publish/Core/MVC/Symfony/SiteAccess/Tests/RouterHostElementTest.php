<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterHostElementTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Host as HostMapMatcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;

class RouterHostElementTest extends PHPUnit_Framework_TestCase
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
                "HostElement" => 2,
                "Map\\URI" => array(
                    "first_sa" => "first_sa",
                    "second_sa" => "second_sa",
                ),
                "Map\\Host" => array(
                    "first_sa" => "first_sa",
                    "first_siteaccess" => "first_sa",
                    "second_sa" => "second_sa",
                ),
            ),
            array( 'first_sa', 'second_sa', 'third_sa', 'fourth_sa', 'fifth_sa', 'example' )
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
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostElement::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostElement::match
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
            array( SimplifiedRequest::fromUrl( "http://www.example.com" ), "example" ),
            array( SimplifiedRequest::fromUrl( "https://www.example.com" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "https://www.example.com/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com//" ), "example" ),
            array( SimplifiedRequest::fromUrl( "https://www.example.com//" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com:8080/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/first_siteaccess/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/?first_siteaccess" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/?first_sa" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/first_salt" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/first_sa.foo" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/test" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/test/foo/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/test/foo/bar/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/test/foo/bar/first_sa" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/default_sa" ), "example" ),

            array( SimplifiedRequest::fromUrl( "http://www.example.com/first_sa" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/first_sa/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com//first_sa//" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com///first_sa///test" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com//first_sa//foo/bar" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com/first_sa/foo" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://www.example.com:82/first_sa/" ), "example" ),
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

            array( SimplifiedRequest::fromUrl( "http://dev.example.com/second_sa" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://dev.example.com/second_sa/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://dev.example.com/second_sa?param1=foo" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://dev.example.com/second_sa/foo/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://dev.example.com:82/second_sa/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://dev.example.com:83/second_sa/" ), "example" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/second_sa/" ), "second_sa" ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Host::getName
     */
    public function testGetName()
    {
        $matcher = new HostMapMatcher( array( 'host' => 'foo' ), array() );
        $this->assertSame( 'host:map', $matcher->getName() );
    }
}
