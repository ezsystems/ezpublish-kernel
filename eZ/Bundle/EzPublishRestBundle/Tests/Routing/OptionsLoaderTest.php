<?php

/**
 * File containing the OptionsLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Routing;

use eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Routing\RouteCollection;

/**
 * @covers \eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader
 */
class OptionsLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $type
     * @param bool $expected
     * @dataProvider getResourceType
     */
    public function testSupportsResourceType($type, $expected)
    {
        self::assertEquals(
            $expected,
            $this->getOptionsLoader()->supports(null, $type)
        );
    }

    public function getResourceType()
    {
        return array(
            array('rest_options', true),
            array('something else', false),
        );
    }

    public function testLoad()
    {
        $optionsRouteCollection = new RouteCollection();

        $this->getRouteCollectionMapperMock()->expects($this->once())
            ->method('mapCollection')
            ->with(new RouteCollection())
            ->will($this->returnValue($optionsRouteCollection));

        self::assertSame(
            $optionsRouteCollection,
            $this->getOptionsLoader()->load('resource', 'rest_options')
        );
    }

    /**
     * Returns a partially mocked OptionsLoader, with the import method mocked.
     *
     * @return OptionsLoader|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOptionsLoader()
    {
        $mock = $this->getMockBuilder('eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader')
            ->setConstructorArgs(array($this->getRouteCollectionMapperMock()))
            ->setMethods(array('import'))
            ->getMock();

        $mock->expects($this->any())
            ->method('import')
            ->with($this->anything(), $this->anything())
            ->will($this->returnValue(new RouteCollection()));

        return $mock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRouteCollectionMapperMock()
    {
        if (!isset($this->routeCollectionMapperMock)) {
            $this->routeCollectionMapperMock = $this->getMockBuilder('eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader\RouteCollectionMapper')
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->routeCollectionMapperMock;
    }
}
