<?php
/**
 * File containing the ConfiguredTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Content\Configured,
    eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher,
    eZ\Publish\API\Repository\Values\Content\Location;

class ConfiguredTest extends BaseTest
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetViewWrongMatcher()
    {
        $lvp = $this->getPartiallyMockedLocationViewProvider(
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
        $lvp
            ->expects( $this->once() )
            ->method( 'getMatcher' )
            ->with( 'wrongMatcher' )
            ->will( $this->returnValue( new \stdClass() ) )
        ;

        $lvp->getView(
            $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
            'full'
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject[] $matchers
     * @param array $matchingConfig
     * @param bool $match
     *
     * @return void
     * @covers eZ\Publish\Core\MVC\Symfony\View\Provider\Content\Configured::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\View\Provider\Content\Configured::getView
     *
     * @dataProvider getViewLocationProvider
     */
    public function testGetViewLocation( array $matchers, array $matchingConfig, $match )
    {
        $lvp = $this->getPartiallyMockedLocationViewProvider( $matchingConfig );
        $lvp
            ->expects(
                $this->exactly( count( $matchers ) )
            )
            ->method( 'getMatcher' )
            ->will(
                $this->onConsecutiveCalls(
                    $matchers[0], $matchers[1]
                )
            )
        ;

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

            $matcherMock1 = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher' );
            $matcherMock1
                ->expects( $this->any() )
                ->method( 'setMatchingConfig' )
                ->with( $matchValue );
            $matcherMock1
                ->expects( $this->any() )
                ->method( 'matchLocation' )
                ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ) )
                ->will( $this->returnValue( true ) )
            ;
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
                    ->will( $this->returnValue( true ) )
                ;
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
}
