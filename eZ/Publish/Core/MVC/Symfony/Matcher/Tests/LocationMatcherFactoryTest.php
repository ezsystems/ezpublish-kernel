<?php
/**
 * File containing the LocationMatcherFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests;

use eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory;

class LocationMatcherFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     */
    public function testMatchFailNoViewType()
    {
        $matcherFactory = new LocationMatcherFactory( $this->getRepositoryMock(), array() );
        $this->assertNull( $matcherFactory->match( $this->getLocationMock(), 'full' ) );
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory::getMatcher
     */
    public function testMatchInvalidMatcher()
    {
        $matcherFactory = new LocationMatcherFactory(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'test' => array(
                        'template' => 'foo.html.twig',
                        'match' => array(
                            'NonExistingMatcher' => true
                        )
                    )
                )
            )
        );
        $matcherFactory->match( $this->getLocationMock(), 'full' );
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory::getMatcher
     */
    public function testMatchNonContentBasedMatcher()
    {
        $matcherFactory = new LocationMatcherFactory(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'test' => array(
                        'template' => 'foo.html.twig',
                        'match' => array(
                            '\\eZ\Publish\Core\MVC\Symfony\Matcher\Block\\Type' => true
                        )
                    )
                )
            )
        );
        $matcherFactory->match( $this->getLocationMock(), 'full' );
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory::doMatch
     */
    public function testMatchInvalidValueObject()
    {
        $matcherFactory = new LocationMatcherFactory(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'test' => array(
                        'template' => 'foo.html.twig',
                        'match' => array(
                            'Id\\Location' => 123
                        )
                    )
                )
            )
        );
        $matcherFactory->match( $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\ValueObject' ), 'full' );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory::doMatch
     */
    public function testMatch()
    {
        $expectedConfigHash = array(
            'template' => 'foo.html.twig',
            'match' => array(
                'Id\\Location' => 456
            )
        );
        $matcherFactory = new LocationMatcherFactory(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'not_matching' => array(
                        'template' => 'bar.html.twig',
                        'match' => array(
                            'Id\\Location' => 123
                        )
                    ),
                    'test' => $expectedConfigHash
                )
            )
        );
        $configHash = $matcherFactory->match( $this->getLocationMock( array( 'id' => 456 ) ), 'full' );
        $this->assertArrayHasKey( 'matcher', $configHash );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\Id\\Location', $configHash['matcher'] );
        // Calling a 2nd time to check if the result has been properly cached in memory
        $this->assertSame(
            $configHash,
            $matcherFactory->match(
                $this->getLocationMock( array( 'id' => 456 ) ),
                'full'
            )
        );

        unset( $configHash['matcher'] );
        $this->assertSame( $expectedConfigHash, $configHash );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory::doMatch
     */
    public function testMatchFail()
    {
        $matcherFactory = new LocationMatcherFactory(
            $this->getRepositoryMock(),
            array(
                'full' => array(
                    'not_matching' => array(
                        'template' => 'bar.html.twig',
                        'match' => array(
                            'Id\\Location' => 123
                        )
                    ),
                    'test' => array(
                        'template' => 'foo.html.twig',
                        'match' => array(
                            'Id\\Location' => 456
                        )
                    )
                )
            )
        );
        $this->assertNull(
            $matcherFactory->match(
                $this->getLocationMock( array( 'id' => 789 ) ),
                'full'
            )
        );
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
        return $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo' )
            ->setConstructorArgs( array( $properties ) )
            ->getMockForAbstractClass();
    }
}
