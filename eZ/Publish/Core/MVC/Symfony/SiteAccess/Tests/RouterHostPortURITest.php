<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterHostPortURITest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Host;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Port;
use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;

class RouterHostPortURITest extends PHPUnit_Framework_TestCase
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

    public function testConstruct()
    {
        return new Router(
            $this->matcherBuilder,
            $this->getMock( 'Psr\\Log\\LoggerInterface' ),
            "default_sa",
            array(
                "Map\\Host" => array(
                    "first_sa" => "first_sa",
                    "first_siteaccess" => "first_sa",
                    "third_siteaccess" => "third_sa",
                ),
                "Map\\Port" => array(
                    80 => "fifth_sa",
                    81 => "third_sa",
                    82 => "fourth_sa",
                    83 => "first_sa",
                    85 => "first_sa",
                    443 => "fourth_sa",
                ),
                "Map\\URI" => array(
                    "first_sa" => "first_sa",
                    "second_sa" => "second_sa",
                ),
            ),
            array( 'first_sa', 'second_sa', 'third_sa', 'fourth_sa', 'fifth_sa' )
        );
    }

    /**
     * @depends testConstruct
     * @dataProvider matchProvider
     */
    public function testMatch( SimplifiedRequest $request, $siteAccess, Router $router )
    {
        $sa = $router->match( $request );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess', $sa );
        $this->assertSame( $siteAccess, $sa->name );
        $router->setSiteAccess();
    }

    public function matchProvider()
    {
        return array(
            array( SimplifiedRequest::fromUrl( "http://example.com" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com/" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com//" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com//" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:8080/" ), "default_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_siteaccess/" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/?first_siteaccess" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/?first_sa" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_salt" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa.foo" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/test/foo/bar/first_sa" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/default_sa" ), "fifth_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa//" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa///test" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/foo" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/first_sa/foo/bar" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/first_sa/" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://third_siteaccess/first_sa/" ), "third_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "https://first_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_sa:81/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/foo/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/foo/" ), "first_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa?param1=foo" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com/second_sa/foo/" ), "fifth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:82/second_sa/" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:83/second_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:82/second_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:83/second_sa/" ), "first_sa" ),

            array( SimplifiedRequest::fromUrl( "http://first_sa:123/second_sa" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://first_siteaccess:123/second_sa/" ), "first_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:123/second_sa?param1=foo" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:123/second_sa/foo/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:123/second_sa" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:123/second_sa/" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:123/second_sa?param1=foo" ), "second_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:123/second_sa/foo/" ), "second_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com:81/" ), "third_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com:81/" ), "third_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:81/foo" ), "third_sa" ),
            array( SimplifiedRequest::fromUrl( "http://example.com:81/foo/bar" ), "third_sa" ),

            array( SimplifiedRequest::fromUrl( "http://example.com:82/" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com:82/" ), "fourth_sa" ),
            array( SimplifiedRequest::fromUrl( "https://example.com:82/foo" ), "fourth_sa" ),
        );
    }

    public function testSetGetRequestMapHost()
    {
        $mapKey = 'phoenix-rises.fm';
        $request = new SimplifiedRequest( array( 'host' => $mapKey ) );
        $matcher = new Host( array( 'foo' => $mapKey ) );
        $matcher->setRequest( $request );
        $this->assertSame( $request, $matcher->getRequest() );
        $this->assertSame( $mapKey, $matcher->getMapKey() );
    }

    public function testReverseHostMatchFail()
    {
        $config = array( 'foo' => 'bar' );
        $matcher = new Host( $config );
        $this->assertNull( $matcher->reverseMatch( 'non_existent' ) );
    }

    public function testReverseMatchHost()
    {
        $config = array(
            'ez.no' => 'some_siteaccess',
            'something_else' => 'another_siteaccess',
            'phoenix-rises.fm' => 'ezdemo_site',
        );
        $request = new SimplifiedRequest( array( 'host' => 'ez.no' ) );
        $matcher = new Host( $config );
        $matcher->setRequest( $request );
        $this->assertSame( 'ez.no', $matcher->getMapKey() );

        $result = $matcher->reverseMatch( 'ezdemo_site' );
        $this->assertInstanceOf( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Host', $result );
        $this->assertSame( $request, $matcher->getRequest() );
        $this->assertSame( 'phoenix-rises.fm', $result->getMapKey() );
        $this->assertSame( 'phoenix-rises.fm', $result->getRequest()->host );
    }

    public function testSetGetRequestMapPort()
    {
        $mapKey = '8000';
        $request = new SimplifiedRequest( array( 'port' => $mapKey ) );
        $matcher = new Port( array( 'foo' => $mapKey ) );
        $matcher->setRequest( $request );
        $this->assertSame( $request, $matcher->getRequest() );
        $this->assertSame( $mapKey, $matcher->getMapKey() );
    }

    public function testReversePortMatchFail()
    {
        $config = array( 'foo' => '8080' );
        $matcher = new Port( $config );
        $this->assertNull( $matcher->reverseMatch( 'non_existent' ) );
    }

    public function testReverseMatchPort()
    {
        $config = array(
            '80' => 'some_siteaccess',
            '443' => 'another_siteaccess',
            8000 => 'ezdemo_site',
        );
        $request = new SimplifiedRequest( array( 'scheme' => 'http', 'host' => 'ez.no' ) );
        $matcher = new Port( $config );
        $matcher->setRequest( $request );
        $this->assertSame( 80, $matcher->getMapKey() );

        $result = $matcher->reverseMatch( 'ezdemo_site' );
        $this->assertInstanceOf( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Port', $result );
        $this->assertSame( $request, $matcher->getRequest() );
        $this->assertSame( 8000, $result->getMapKey() );
        $this->assertSame( 8000, $result->getRequest()->port );
        $this->assertSame( 'http', $result->getRequest()->scheme );
    }
}
