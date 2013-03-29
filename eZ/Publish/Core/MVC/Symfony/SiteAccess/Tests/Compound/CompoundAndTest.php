<?php
/**
 * File containing the CompoundAndTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\Compound;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder;

class CompoundAndTest extends \PHPUnit_Framework_TestCase
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
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound::__construct
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
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound::setMatcherBuilder
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound::getSubMatchers
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
     * @covers \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound\LogicalAnd::match
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
}
