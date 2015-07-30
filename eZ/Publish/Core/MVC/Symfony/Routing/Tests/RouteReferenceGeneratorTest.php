<?php

/**
 * File containing the RouteReferenceGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RouteReferenceGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    protected function setUp()
    {
        parent::setUp();
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    public function testGenerateNullResource()
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = array('foo' => 'bar');

        $request = new Request();
        $request->attributes->set('_route', $currentRouteName);
        $request->attributes->set('_route_params', $currentRouteParams);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $event = new RouteReferenceGenerationEvent(new RouteReference($currentRouteName, $currentRouteParams), $request);
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::ROUTE_REFERENCE_GENERATION, $this->equalTo($event));

        $generator = new RouteReferenceGenerator($this->dispatcher);
        $generator->setRequestStack($requestStack);
        $reference = $generator->generate();
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Routing\RouteReference', $reference);
        $this->assertSame($currentRouteName, $reference->getRoute());
        $this->assertSame($currentRouteParams, $reference->getParams());
    }

    public function testGenerateNullResourceAndPassedParams()
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = array('foo' => 'bar');
        $passedParams = array('hello' => 'world', 'isIt' => true);
        $expectedParams = $passedParams + $currentRouteParams;

        $request = new Request();
        $request->attributes->set('_route', $currentRouteName);
        $request->attributes->set('_route_params', $currentRouteParams);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $event = new RouteReferenceGenerationEvent(new RouteReference($currentRouteName, $expectedParams), $request);
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::ROUTE_REFERENCE_GENERATION, $this->equalTo($event));

        $generator = new RouteReferenceGenerator($this->dispatcher);
        $generator->setRequestStack($requestStack);
        $reference = $generator->generate(null, $passedParams);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Routing\RouteReference', $reference);
        $this->assertSame($currentRouteName, $reference->getRoute());
        $this->assertSame($expectedParams, $reference->getParams());
    }

    /**
     * @dataProvider generateGenerator
     */
    public function testGenerate($resource, array $params)
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = array('foo' => 'bar');

        $request = new Request();
        $request->attributes->set('_route', $currentRouteName);
        $request->attributes->set('_route_params', $currentRouteParams);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $event = new RouteReferenceGenerationEvent(new RouteReference($resource, $params), $request);
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::ROUTE_REFERENCE_GENERATION, $this->equalTo($event));

        $generator = new RouteReferenceGenerator($this->dispatcher);
        $generator->setRequestStack($requestStack);
        $reference = $generator->generate($resource, $params);
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Routing\RouteReference', $reference);
        $this->assertSame($resource, $reference->getRoute());
        $this->assertSame($params, $reference->getParams());
    }

    public function testGenerateNullResourceWithoutRoute()
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = array('foo' => 'bar');

        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $event = new RouteReferenceGenerationEvent(new RouteReference(null, array()), $request);
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::ROUTE_REFERENCE_GENERATION, $this->equalTo($event));

        $generator = new RouteReferenceGenerator($this->dispatcher);
        $generator->setRequestStack($requestStack);
        $reference = $generator->generate();
        $this->assertInstanceOf('eZ\Publish\Core\MVC\Symfony\Routing\RouteReference', $reference);
    }

    public function generateGenerator()
    {
        return array(
            array('my_route', array('hello' => 'world', 'isIt' => true)),
            array('foobar', array('foo' => 'bar', 'object' => new \stdClass())),
            array(new Location(), array('switchLanguage' => 'fre-FR')),
            array(new Location(), array()),
        );
    }
}
