<?php
/**
 * File containing the ConfiguredTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\BlockViewProvider\Configured;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured as BlockViewProvider;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher;

class ConfiguredTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRepositoryMock()
    {
        return $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBlockMock()
    {
        return $this->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::getView
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::getMatcher
     */
    public function testGetViewWrongMatcher()
    {
        $bvp = new BlockViewProvider(
            $this->getRepositoryMock(),
            array(
                'failingMatchBlock' => array(
                    'match'    => array(
                        'wrongMatcher' => 'bibou est un gentil garçon'
                    ),
                    'template' => "mytemplate"
                )
            )
        );

        $bvp->getView(
            $this->getBlockMock(),
            'full'
        );
    }

    /**
     * @param array $matchingConfig
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartiallyMockedViewProvider( array $matchingConfig = array() )
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\Provider\\Block\\Configured' )
            ->setConstructorArgs(
                array(
                    $this->getRepositoryMock(),
                    $matchingConfig
                )
            )
            ->setMethods( array( 'getMatcher' ) )
            ->getMock();
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject[] $matchers
     * @param array $matchingConfig
     * @param boolean $match
     *
     * @return void
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::getView
     *
     * @dataProvider getViewBlockProvider
     */
    public function testGetViewBlock( array $matchers, array $matchingConfig, $match )
    {
        $bvp = $this->getPartiallyMockedViewProvider( $matchingConfig );
        $bvp
            ->expects(
                $this->exactly( count( $matchers ) )
            )
            ->method( 'getMatcher' )
            ->will(
                $this->onConsecutiveCalls(
                    $matchers[0], $matchers[1]
                )
            );

        $contentView = $bvp->getView( $this->getBlockMock() );
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
    public function getViewBlockProvider()
    {
        $arguments = array();
        for ( $i = 0; $i < 10; ++$i )
        {
            $matchValue = "foo-$i";
            $matchers = array();
            $matchingConfig = array();
            $doMatch = true;

            $matcherMock1 = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\BlockViewProvider\\Configured\\Matcher' );
            $matcherMock1
                ->expects( $this->any() )
                ->method( 'setMatchingConfig' )
                ->with( $matchValue );
            $matcherMock1
                ->expects( $this->any() )
                ->method( 'matchBlock' )
                ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' ) )
                ->will( $this->returnValue( true ) );
            $matchers[] = $matcherMock1;
            $matchingConfig[get_class( $matcherMock1 )] = $matchValue;

            // Introducing a failing matcher every even iteration
            if ( $i % 2 == 0 )
            {
                $failingMatcher = clone $matcherMock1;
                $failingMatcher
                    ->expects( $this->once() )
                    ->method( 'matchBlock' )
                    ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' ) )
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
                    "matchingBlock-$i" => array(
                        'match'   => $matchingConfig,
                        'template' => "mytemplate-$i"
                    )
                ),
                $doMatch
            );
        }

        return $arguments;
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::match
     */
    public function testMatch()
    {
        $matcherMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\BlockViewProvider\\Configured\\Matcher' );
        $blockMock = $this->getBlockMock();
        $matcherMock
            ->expects( $this->once() )
            ->method( 'matchBlock' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' ) );

        $bvp = new BlockViewProvider( $this->getRepositoryMock(), array() );
        $bvp->match( $matcherMock, $blockMock );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::match
     * @expectedException \InvalidArgumentException
     */
    public function testMatchWrongValueObject()
    {
        $matcherMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher' );
        $wrongObject = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' );

        $bvp = new BlockViewProvider( $this->getRepositoryMock(), array() );
        $bvp->match( $matcherMock, $wrongObject );
    }
}
