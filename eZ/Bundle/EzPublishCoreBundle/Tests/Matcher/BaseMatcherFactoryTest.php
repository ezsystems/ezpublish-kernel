<?php
/**
 * File containing the BaseMatcherFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

abstract class BaseMatcherFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $matcherServiceIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResolverMock( $matcherServiceIdentifier )
    {
        $resolverMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $resolverMock
        ->expects( $this->atLeastOnce() )
        ->method( 'getParameter' )
        ->with( $this->logicalOr( 'location_view', 'content_view' ) )
        ->will(
            $this->returnValue(
                array(
                    'full' => array(
                        'matchRule' => array(
                            'template'    => 'my_template.html.twig',
                            'match'            => array(
                                $matcherServiceIdentifier   => 'someValue'
                            )
                        )
                    )
                )
            )
        );

        return $resolverMock;
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
