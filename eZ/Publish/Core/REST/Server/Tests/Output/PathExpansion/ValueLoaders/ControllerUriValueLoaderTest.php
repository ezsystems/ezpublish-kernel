<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\PathExpansion\ValueLoaders;

use eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\ControllerUriValueLoader;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;

class ControllerUriValueLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\ControllerUriValueLoader
     */
    private $loader;

    /**
     * @var \Symfony\Component\Routing\RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $routerMock;

    /**
     * @var \Symfony\Component\Routing\RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $controllerResolverMock;

    public function setUp()
    {
        $this->loader = new ControllerUriValueLoader(
            $this->routerMock = $this->getMock('Symfony\Component\Routing\RouterInterface'),
            $this->controllerResolverMock = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface')
        );
    }

    /**
     * Covers the normal behaviour of the load method.
     */
    public function testLoad()
    {
        $controllerArray = ['_controller' => 'some:controller', 'id' => 1];

        $valueObject = new \stdClass();

        $this->routerMock
            ->expects($this->once())
            ->method('match')
            ->with('/api/ezp/v2/resource/1')
            ->will($this->returnValue($controllerArray));

        $this->controllerResolverMock
            ->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(
                function () use ($valueObject) { return $valueObject; }
            ));

        $this->controllerResolverMock
            ->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue(['id' => 1]));

        self::assertSame(
            $valueObject,
            $this->loader->load('/api/ezp/v2/resource/1')
        );
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Expected the controller to return a Value object, got a Response instead
     */
    public function testLoadReturnsResponse()
    {
        $controllerArray = ['_controller' => 'some:controller', 'id' => 1];

        $valueObject = new Response();

        $this->routerMock
            ->expects($this->once())
            ->method('match')
            ->with('/api/ezp/v2/resource/1')
            ->will($this->returnValue($controllerArray));

        $this->controllerResolverMock
            ->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(
                function () use ($valueObject) { return $valueObject; }
            ));

        $this->controllerResolverMock
            ->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue(['id' => 1]));

        $this->loader->load('/api/ezp/v2/resource/1');
    }
}
