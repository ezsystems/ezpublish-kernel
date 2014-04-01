<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\RouterTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use PHPUnit_Framework_TestCase;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;
use Symfony\Component\HttpFoundation\Request;

class RouterTest extends PHPUnit_Framework_TestCase
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

    protected function tearDown()
    {
        putenv( 'EZPUBLISH_SITEACCESS' );
        parent::tearDown();
    }

    public function testConstruct()
    {
        return new Router(
            $this->matcherBuilder,
            $this->getMock( 'Psr\\Log\\LoggerInterface' ),
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
                'Compound\\LogicalAnd' => array(
                    array(
                        'matchers'  => array(
                            'Map\\URI' => array( 'eng' => true ),
                            'Map\\Host' => array( 'fr.ezpublish.dev' => true )
                        ),
                        'match'     => 'fr_eng'
                    ),
                    array(
                        'matchers'  => array(
                            'Map\\URI' => array( 'fre' => true ),
                            'Map\\Host' => array( 'us.ezpublish.dev' => true )
                        ),
                        'match'     => 'fr_us'
                    ),
                ),
            ),
            array( 'first_sa', 'second_sa', 'third_sa', 'fourth_sa', 'headerbased_sa', 'fr_eng', 'fr_us' )
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
        // SiteAccess must be serializable as a whole
        // See https://jira.ez.no/browse/EZP-21613
        $this->assertInternalType( 'string', serialize( $sa ) );
        $router->setSiteAccess();
    }

    /**
     * @depends testConstruct
     * @expectedException \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     */
    public function testMatchWithEnvFail( Router $router )
    {
        $saName = 'foobar_sa';
        putenv( "EZPUBLISH_SITEACCESS=$saName" );
        $router->match( new SimplifiedRequest() );
    }

    /**
     * @depends testConstruct
     */
    public function testMatchWithEnv( Router $router )
    {
        $saName = 'first_sa';
        putenv( "EZPUBLISH_SITEACCESS=$saName" );
        $sa = $router->match( new SimplifiedRequest() );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess', $sa );
        $this->assertSame( $saName, $sa->name );
        $this->assertSame( 'env', $sa->matchingType );
        $router->setSiteAccess();
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router $router
     *
     * @depends testConstruct
     */
    public function testMatchWithRequestHeader( Router $router )
    {
        $saName = 'headerbased_sa';
        $request = Request::create( '/foo/bar' );
        $request->headers->set( 'X-Siteaccess', $saName );
        $sa = $router->match(
            new SimplifiedRequest(
                array(
                    'headers' => $request->headers->all()
                )
            )
        );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess', $sa );
        $this->assertSame( $saName, $sa->name );
        $this->assertSame( 'header', $sa->matchingType );
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

            array( SimplifiedRequest::fromUrl( 'http://fr.ezpublish.dev/eng' ), 'fr_eng' ),
            array( SimplifiedRequest::fromUrl( 'http://us.ezpublish.dev/fre' ), 'fr_us' ),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMatchByNameInvalidSiteAccess()
    {
        $matcherBuilder = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilderInterface' );
        $logger = $this->getMock( 'Psr\Log\LoggerInterface' );
        $router = new Router( $matcherBuilder, $logger, 'default_sa', array(), array( 'foo', 'default_sa' ) );
        $router->matchByName( 'bar' );
    }

    public function testMatchByName()
    {
        $matcherBuilder = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilderInterface' );
        $logger = $this->getMock( 'Psr\Log\LoggerInterface' );
        $matcherClass = 'Map\Host';
        $matchedSiteAccess = 'foo';
        $matcherConfig = array(
            'phoenix-rises.fm' => $matchedSiteAccess,
        );
        $config = array(
            $matcherClass => $matcherConfig,
            'Map\URI' => array( 'default' => 'default_sa' )
        );

        $router = new Router( $matcherBuilder, $logger, 'default_sa', $config, array( $matchedSiteAccess, 'default_sa' ) );
        $request = $router->getRequest();
        $matcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $matcherBuilder
            ->expects( $this->once() )
            ->method( 'buildMatcher' )
            ->with( $matcherClass, $matcherConfig, $request )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'Map\URI', array( 'default' => 'default_sa' ), $request, $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher' ) ),
                        array( $matcherClass, $matcherConfig, $request, $matcher ),
                    )
                )
            );

        $reverseMatchedMatcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $matcher
            ->expects( $this->once() )
            ->method( 'reverseMatch' )
            ->with( $matchedSiteAccess )
            ->will( $this->returnValue( $reverseMatchedMatcher ) );

        $siteAccess = $router->matchByName( $matchedSiteAccess );
        $this->assertInstanceOf( 'eZ\Publish\Core\MVC\Symfony\SiteAccess', $siteAccess );
        $this->assertSame( $reverseMatchedMatcher, $siteAccess->matcher );
        $this->assertSame( $matchedSiteAccess, $siteAccess->name );
    }

    public function testMatchByNameNoVersatileMatcher()
    {
        $matcherBuilder = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilderInterface' );
        $logger = $this->getMock( 'Psr\Log\LoggerInterface' );
        $matcherClass = 'Map\Host';
        $matchedSiteAccess = 'foo';
        $matcherConfig = array(
            'phoenix-rises.fm' => $matchedSiteAccess,
            'ez.no' => 'default_sa',
        );
        $config = array( $matcherClass => $matcherConfig );

        $router = new Router( $matcherBuilder, $logger, 'default_sa', $config, array( $matchedSiteAccess, 'default_sa' ) );
        $request = $router->getRequest();
        $matcherBuilder
            ->expects( $this->once() )
            ->method( 'buildMatcher' )
            ->with( $matcherClass, $matcherConfig, $request )
            ->will( $this->returnValue( $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher' ) ) );

        $logger
            ->expects( $this->once() )
            ->method( 'notice' );
        $this->assertNull( $router->matchByName( $matchedSiteAccess ) );
    }
}
