<?php

/**
 * File containing the BaseMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

use PHPUnit_Framework_TestCase;

abstract class BaseMatcherFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $matcherServiceIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResolverMock($matcherServiceIdentifier)
    {
        $resolverMock = $this->getMock('eZ\\Publish\\Core\\MVC\\ConfigResolverInterface');
        $resolverMock
        ->expects($this->atLeastOnce())
        ->method('getParameter')
        ->with($this->logicalOr('location_view', 'content_view', 'block_view'))
        ->will(
            $this->returnValue(
                array(
                    'full' => array(
                        'matchRule' => array(
                            'template' => 'my_template.html.twig',
                            'match' => array(
                                $matcherServiceIdentifier => 'someValue',
                            ),
                        ),
                    ),
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
    protected function getLocationMock(array $properties = array())
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Location')
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContentInfoMock(array $properties = array())
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo')
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
    }

    /**
     * @param array $properties
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBlockMock(array $properties = array())
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block')
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
    }
}
