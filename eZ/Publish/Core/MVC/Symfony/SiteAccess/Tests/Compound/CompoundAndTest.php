<?php
/**
 * File containing the CompoundAndTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\Compound;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;
use PHPUnit_Framework_TestCase;

class CompoundAndTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $matcherBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->matcherBuilder = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\MatcherBuilderInterface' );
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd
     */
    public function testConstruct()
    {
        return $this->buildMatcher();
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd
     */
    private function buildMatcher()
    {
        return new LogicalAnd(
            array(
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
                array(
                    'matchers'  => array(
                        'Map\\URI' => array( 'de' => true ),
                        'Map\\Host' => array( 'jp.ezpublish.dev' => true )
                    ),
                    'match'     => 'de_jp'
                ),
            )
        );
    }

    /**
     * @depends testConstruct
     */
    public function testSetMatcherBuilder( Compound $compoundMatcher )
    {
        $this
            ->matcherBuilder
            ->expects( $this->any() )
            ->method( 'buildMatcher' )
            ->will( $this->returnValue( $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\Matcher' ) ) );

        $compoundMatcher->setRequest( $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Routing\\SimplifiedRequest' ) );
        $compoundMatcher->setMatcherBuilder( $this->matcherBuilder );
        $matchers = $compoundMatcher->getSubMatchers();
        $this->assertInternalType( 'array', $matchers );
        foreach ( $matchers as $matcher )
        {
            $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess\\Matcher', $matcher );
        }
    }

    /**
     * @dataProvider matchProvider
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     * @param $expectedMatch
     */
    public function testMatch( SimplifiedRequest $request, $expectedMatch )
    {
        $compoundMatcher = $this->buildMatcher();
        $compoundMatcher->setRequest( $request );
        $compoundMatcher->setMatcherBuilder( new MatcherBuilder() );
        $this->assertSame( $expectedMatch, $compoundMatcher->match() );
    }

    public function testSetRequest()
    {
        $compoundMatcher = new LogicalAnd(
            array(
                array(
                    'matchers'  => array(
                        'Map\\URI' => array( 'eng' => true ),
                        'Map\\Host' => array( 'fr.ezpublish.dev' => true )
                    ),
                    'match'     => 'fr_eng'
                ),
            )
        );

        $matcher1 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher' );
        $matcher2 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher' );
        $this->matcherBuilder
            ->expects( $this->exactly( 2 ) )
            ->method( 'buildMatcher' )
            ->will( $this->onConsecutiveCalls( $matcher1, $matcher2 ) );

        $request = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest' );
        $matcher1
            ->expects( $this->once() )
            ->method( 'setRequest' )
            ->with( $request );
        $matcher2
            ->expects( $this->once() )
            ->method( 'setRequest' )
            ->with( $request );

        $compoundMatcher->setRequest( $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest' ) );
        $compoundMatcher->setMatcherBuilder( $this->matcherBuilder );
        $compoundMatcher->setRequest( $request );
    }

    public function matchProvider()
    {
        return array(
            array( SimplifiedRequest::fromUrl( 'http://fr.ezpublish.dev/eng' ), 'fr_eng' ),
            array( SimplifiedRequest::fromUrl( 'http://ezpublish.dev/eng' ), false ),
            array( SimplifiedRequest::fromUrl( 'http://fr.ezpublish.dev/fre' ), false ),
            array( SimplifiedRequest::fromUrl( 'http://fr.ezpublish.dev/' ), false ),
            array( SimplifiedRequest::fromUrl( 'http://us.ezpublish.dev/eng' ), false ),
            array( SimplifiedRequest::fromUrl( 'http://us.ezpublish.dev/fre' ), 'fr_us' ),
            array( SimplifiedRequest::fromUrl( 'http://ezpublish.dev/fr' ), false ),
            array( SimplifiedRequest::fromUrl( 'http://jp.ezpublish.dev/de' ), 'de_jp' ),
        );
    }

    public function testReverseMatchSiteAccessNotConfigured()
    {
        $compoundMatcher = $this->buildMatcher();
        $this->matcherBuilder
            ->expects( $this->any() )
            ->method( 'buildMatcher' )
            ->will( $this->returnValue( $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' ) ) );

        $compoundMatcher->setRequest( $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest' ) );
        $compoundMatcher->setMatcherBuilder( $this->matcherBuilder );
        $this->assertNull( $compoundMatcher->reverseMatch( 'not_configured_sa' ) );
    }

    public function testReverseMatchNotVersatile()
    {
        $request = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest' );
        $siteAccessName = 'fr_eng';
        $mapUriConfig = array( 'eng' => true );
        $mapHostConfig = array( 'fr.ezpublish.dev' => true );
        $compoundMatcher = new LogicalAnd(
            array(
                array(
                    'matchers'  => array(
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig
                    ),
                    'match'     => $siteAccessName
                ),
            )
        );
        $compoundMatcher->setRequest( $request );

        $matcher1 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $matcher2 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher' );
        $this->matcherBuilder
            ->expects( $this->exactly( 2 ) )
            ->method( 'buildMatcher' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'Map\URI', $mapUriConfig, $request, $matcher1 ),
                        array( 'Map\Host', $mapHostConfig, $request, $matcher2 ),
                    )
                )
            );

        $matcher1
            ->expects( $this->once() )
            ->method( 'setRequest' )
            ->with( $request );
        $matcher1
            ->expects( $this->once() )
            ->method( 'reverseMatch' )
            ->with( $siteAccessName )
            ->will( $this->returnValue( $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' ) ) );
        $matcher2
            ->expects( $this->never() )
            ->method( 'setRequest' );
        $matcher2
            ->expects( $this->never() )
            ->method( 'reverseMatch' );

        $compoundMatcher->setMatcherBuilder( $this->matcherBuilder );
        $this->assertNull( $compoundMatcher->reverseMatch( $siteAccessName ) );
    }

    public function testReverseMatchFail()
    {
        $request = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest' );
        $siteAccessName = 'fr_eng';
        $mapUriConfig = array( 'eng' => true );
        $mapHostConfig = array( 'fr.ezpublish.dev' => true );
        $compoundMatcher = new LogicalAnd(
            array(
                array(
                    'matchers'  => array(
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig
                    ),
                    'match'     => $siteAccessName
                ),
            )
        );
        $compoundMatcher->setRequest( $request );

        $matcher1 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $matcher2 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $this->matcherBuilder
            ->expects( $this->exactly( 2 ) )
            ->method( 'buildMatcher' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'Map\URI', $mapUriConfig, $request, $matcher1 ),
                        array( 'Map\Host', $mapHostConfig, $request, $matcher2 ),
                    )
                )
            );

        $matcher1
            ->expects( $this->once() )
            ->method( 'setRequest' )
            ->with( $request );
        $matcher1
            ->expects( $this->once() )
            ->method( 'reverseMatch' )
            ->with( $siteAccessName )
            ->will( $this->returnValue( $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' ) ) );
        $matcher2
            ->expects( $this->once() )
            ->method( 'setRequest' )
            ->with( $request );
        $matcher2
            ->expects( $this->once() )
            ->method( 'reverseMatch' )
            ->with( $siteAccessName )
            ->will( $this->returnValue( null ) );

        $compoundMatcher->setMatcherBuilder( $this->matcherBuilder );
        $this->assertNull( $compoundMatcher->reverseMatch( $siteAccessName ) );
    }

    public function testReverseMatch()
    {
        $request = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest' );
        $siteAccessName = 'fr_eng';
        $mapUriConfig = array( 'eng' => true );
        $mapHostConfig = array( 'fr.ezpublish.dev' => true );
        $compoundMatcher = new LogicalAnd(
            array(
                array(
                    'matchers'  => array(
                        'Map\URI' => $mapUriConfig,
                        'Map\Host' => $mapHostConfig
                    ),
                    'match'     => $siteAccessName
                ),
            )
        );
        $compoundMatcher->setRequest( $request );

        $matcher1 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $matcher2 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $this->matcherBuilder
            ->expects( $this->exactly( 2 ) )
            ->method( 'buildMatcher' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'Map\URI', $mapUriConfig, $request, $matcher1 ),
                        array( 'Map\Host', $mapHostConfig, $request, $matcher2 ),
                    )
                )
            );

        $matcher1
            ->expects( $this->once() )
            ->method( 'setRequest' )
            ->with( $request );
        $reverseMatchedMatcher1 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $matcher1
            ->expects( $this->once() )
            ->method( 'reverseMatch' )
            ->with( $siteAccessName )
            ->will( $this->returnValue( $reverseMatchedMatcher1 ) );
        $matcher2
            ->expects( $this->once() )
            ->method( 'setRequest' )
            ->with( $request );
        $reverseMatchedMatcher2 = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher' );
        $matcher2
            ->expects( $this->once() )
            ->method( 'reverseMatch' )
            ->with( $siteAccessName )
            ->will( $this->returnValue( $reverseMatchedMatcher2 ) );

        $compoundMatcher->setMatcherBuilder( $this->matcherBuilder );
        $result = $compoundMatcher->reverseMatch( $siteAccessName );
        $this->assertInstanceOf( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd', $result );
        foreach ( $result->getSubMatchers() as $subMatcher )
        {
            $this->assertInstanceOf( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher', $subMatcher );
        }
    }
}
