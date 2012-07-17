<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Tests\RouterTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Tests;
use PHPUnit_Framework_TestCase,
    eZ\Publish\MVC\SiteAccess\Router,
    eZ\Publish\MVC\Routing\SimplifiedRequest;

class RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\MVC\SiteAccess\Router::__construct
     */
    public function testConstruct()
    {
        return new Router(
            "default_sa",
            array(
                "Map\\URI" => array(
                    "first_sa" => "first_sa",
                    "second_sa" => "second_sa",
                ),
                "Map\\Host" => array(
                    "first_sa" => "first_sa",
                    "first_siteaccess" => "first_sa",
                    "third_siteaccess" => "third_sa",
                ),
                "Map\\Port" => array(
                    81 => "third_sa",
                    82 => "fourth_sa",
                    83 => "first_sa",
                    85 => "first_sa",
                ),
            )
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider matchProvider
     * @covers \eZ\Publish\MVC\SiteAccess\Router::match
     * @covers \eZ\Publish\MVC\SiteAccess\Matcher\Map::__construct
     * @covers \eZ\Publish\MVC\SiteAccess\Matcher\Map::match
     * @covers \eZ\Publish\MVC\SiteAccess\Matcher\Map\URI::__construct
     * @covers \eZ\Publish\MVC\SiteAccess\Matcher\Map\Host::__construct
     * @covers \eZ\Publish\MVC\SiteAccess\Matcher\Map\Port::__construct
     */
    public function testMatch( $request, $siteAccess, $router )
    {
        $sa = $router->match( $request );
        $this->assertInstanceOf( 'eZ\\Publish\\MVC\\SiteAccess', $sa );
        $this->assertSame( $siteAccess, $sa->name );
    }

    /**
     * @depends testConstruct
     * @covers \eZ\Publish\MVC\SiteAccess\Router::match
     */
    public function testMatchWithEnv( $router )
    {
        $_ENV['EZPUBLISH_SITEACCESS'] = 'foobar_sa';
        $sa = $router->match( new SimplifiedRequest() );
        $this->assertInstanceOf( 'eZ\\Publish\\MVC\\SiteAccess', $sa );
        $this->assertSame( $_ENV['EZPUBLISH_SITEACCESS'], $sa->name );
        $this->assertSame( 'env', $sa->matchingType );
        unset( $_ENV['EZPUBLISH_SITEACCESS'] );
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
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa//" ), "first_sa" ),
            // Double slashes shouldn't be considered as one
            array( SimplifiedRequest::fromUrl( "http://example.com//first_sa//" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa///test" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/foo" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/foo/bar" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://third_siteaccess/first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "https://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:81/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/foo/" ), "first_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa?param1=foo" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/foo/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:83/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/second_sa/" ), "second_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com:81/" ), "third_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com:81/" ), "third_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:81/foo" ), "third_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:81/foo/bar" ), "third_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com:82/" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com:82/" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com:82/foo" ), "fourth_sa" ),
        );
    }
}
