<?php
/**
 * File containing the ConfiguredTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured as LocationViewProvider;

class ConfiguredTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    protected function setUp()
    {
        parent::setUp();
        $this->markTestSkipped( 'To be refactored.' );
        $this->repositoryMock = $this->getRepositoryMock();
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Content\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Content\Configured::getView
     */
    public function testGetViewLocationWrongMatcher()
    {
        $lvp = new LocationViewProvider(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'failingMatchBlock' => array(
                        'match'    => array(
                            'wrongMatcher' => 'bibou est un gentil garçon'
                        ),
                        'template' => "mytemplate"
                    )
                )
            )
        );

        $lvp->getView(
            $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
            'full'
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject[] $matchers
     * @param array $matchingConfig
     * @param boolean $match
     *
     * @return void
     * @covers eZ\Publish\Core\MVC\Symfony\View\Provider\Content\Configured::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\View\Provider\Content\Configured::getView
     *
     * @dataProvider getViewLocationProvider
     */
    public function testGetViewLocation( array $matchers, array $matchingConfig, $match )
    {
        $lvp = $this->getPartiallyMockedViewProvider( $matchingConfig );
        $lvp
            ->expects(
                $this->exactly( count( $matchers ) )
            )
            ->method( 'getMatcher' )
            ->will(
                $this->onConsecutiveCalls(
                    $matchers[0], $matchers[1]
                )
            );

        $contentView = $lvp->getView(
            $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
            'full'
        );
        if ( $match )
            $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewInterface', $contentView );
        else
            $this->assertNull( $contentView );
    }

    /**
     * Provides a configuration with different matchers.
     * One on two set of matchers will force matching to fail.
     *
     * @return array
     */
    public function getViewLocationProvider()
    {
        $arguments = array();
        for ( $i = 0; $i < 10; ++$i )
        {
            $matchValue = "foo-$i";
            $matchers = array();
            $matchingConfig = array();
            $doMatch = true;

            $matcherMock1 = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface' );
            $matcherMock1
                ->expects( $this->any() )
                ->method( 'setMatchingConfig' )
                ->with( $matchValue );
            $matcherMock1
                ->expects( $this->any() )
                ->method( 'matchLocation' )
                ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ) )
                ->will( $this->returnValue( true ) );
            $matchers[] = $matcherMock1;
            $matchingConfig[get_class( $matcherMock1 )] = $matchValue;

            // Introducing a failing matcher every even iteration
            if ( $i % 2 == 0 )
            {
                $failingMatcher = clone $matcherMock1;
                $failingMatcher
                    ->expects( $this->once() )
                    ->method( 'matchLocation' )
                    ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ) )
                    ->will( $this->returnValue( true ) );
                $matchers[] = $failingMatcher;
                $matchingConfig[get_class( $failingMatcher ) . 'failing'] = $matchValue;
            }
            else
            {
                // Cloning the first mock as it is supposed to match as well.
                $matcherMock2 = clone $matcherMock1;
                $matchers[] = $matcherMock2;
                $matchingConfig[get_class( $matcherMock2 ) . 'second'] = $matchValue;
            }

            $arguments[] = array(
                $matchers,
                array(
                    'full' => array(
                        "matchingBlock-$i" => array(
                            'match'   => $matchingConfig,
                            'template' => "mytemplate-$i"
                        )
                    )
                ),
                $doMatch
            );
        }

        return $arguments;
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured::match
     */
    public function testMatch()
    {
        $matcherMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface' );
        $locationMock = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' );
        $matcherMock
            ->expects( $this->once() )
            ->method( 'matchLocation' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ) );

        $lvp = new LocationViewProvider( $this->getRepositoryMock(), array() );
        $lvp->match( $matcherMock, $locationMock );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured::match
     * @expectedException \InvalidArgumentException
     */
    public function testMatchWrongValueObject()
    {
        $matcherMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface' );
        $locationMock = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );

        $lvp = new LocationViewProvider( $this->getRepositoryMock(), array() );
        $lvp->match( $matcherMock, $locationMock );
    }

    /**
     * @param array $matchingConfig
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartiallyMockedViewProvider( array $matchingConfig = array() )
    {
        return $this
               ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Location\\Configured' )
               ->setConstructorArgs(
                       array(
                            $this->repositoryMock,
                            $matchingConfig
                       )
                   )
               ->setMethods( array( 'getMatcher' ) )
               ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        return $this
               ->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\Repository' )
               ->disableOriginalConstructor()
               ->getMock();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLocationMock( array $properties = array() )
    {
        return $this
               ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' )
               ->setConstructorArgs( array( $properties ) )
               ->getMockForAbstractClass();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentInfoMock( array $properties = array() )
    {
        return $this->
                   getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' )
               ->setConstructorArgs( array( $properties ) )
               ->getMockForAbstractClass();
    }
}
