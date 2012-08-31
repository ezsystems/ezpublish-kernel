<?php
/**
 * File containing the ConfiguredTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured,
    eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher,
    eZ\Publish\API\Repository\Values\Content\Location;

class ConfiguredTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    protected function setUp()
    {
        parent::setUp();
        $this->repositoryMock = $this->getRepositoryMock();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetViewForLocationWrongMatcher()
    {
        $cvp = $this->getPartiallyMockedContentViewProvider(
            array(
                 array(
                     'viewType'      => 'full',
                     'match'         => array(
                         'wrongMatcher' => 'bibou est un gentil garçon'
                     ),
                     'matchTemplate' => "mytemplate"
                 )
            )
        );
        $cvp
            ->expects( $this->once() )
            ->method( 'getMatcher' )
            ->with( 'wrongMatcher' )
            ->will( $this->returnValue( new \stdClass() ) )
        ;

        $cvp->getViewForLocation(
            $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
            'full'
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $matcher
     * @param array $locationMatchingConfig
     *
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured::getViewForLocation
     *
     * @dataProvider getViewForLocationProvider
     */
    public function testGetViewForLocation( $matcher, $locationMatchingConfig )
    {
        $cvp = $this->getPartiallyMockedContentViewProvider( $locationMatchingConfig );
        $cvp
            ->expects( $this->once() )
            ->method( 'getMatcher' )
            ->with( get_class( $matcher ) )
            ->will( $this->returnValue( $matcher ) )
        ;

        $contentView = $cvp->getViewForLocation(
            $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
            'full'
        );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewInterface', $contentView );
    }

    public function getViewForLocationProvider()
    {
        $arguments = array();
        for ( $i = 0; $i < 10; ++$i )
        {
            $matchValue = "foo-$i";
            $matcherMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher' );
            $matcherMock
                ->expects( $this->any() )
                ->method( 'setMatchingConfig' )
                ->with( $matchValue );
            $matcherMock
                ->expects( $this->any() )
                ->method( 'matchLocation' )
                ->will( $this->returnValue( true ) )
            ;
            $arguments[] = array(
                $matcherMock,
                array(
                    array(
                        'viewType'      => 'full',
                        'match'         => array(
                            get_class( $matcherMock ) => $matchValue
                        ),
                        'matchTemplate' => "mytemplate-$i"
                    )
                )
            );
        }

        return $arguments;
    }

    /**
     * @param array $locationMatchingConfig
     * @param array $contentMatchingConfig
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPartiallyMockedContentViewProvider( array $locationMatchingConfig = array(), $contentMatchingConfig = array() )
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured' )
            ->setConstructorArgs(
            array(
                 $this->repositoryMock,
                 $locationMatchingConfig,
                 $contentMatchingConfig )
            )
            ->setMethods( array( 'getMatcher' ) )
            ->getMock()
        ;
    }

    private function getRepositoryMock()
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\Repository' )
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
