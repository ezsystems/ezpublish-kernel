<?php

/**
 * File containing the RouteReferenceGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RouteReferenceGeneratorTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $dispatcher;

    protected function setUp()
    {
        parent::setUp();
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testGenerateNullResource()
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = ['foo' => 'bar'];

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
        $this->assertInstanceOf(RouteReference::class, $reference);
        $this->assertSame($currentRouteName, $reference->getRoute());
        $this->assertSame($currentRouteParams, $reference->getParams());
    }

    public function testGenerateNullResourceAndPassedParams()
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = ['foo' => 'bar'];
        $passedParams = ['hello' => 'world', 'isIt' => true];
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
        $this->assertInstanceOf(RouteReference::class, $reference);
        $this->assertSame($currentRouteName, $reference->getRoute());
        $this->assertSame($expectedParams, $reference->getParams());
    }

    /**
     * @dataProvider generateGenerator
     */
    public function testGenerate($resource, array $params)
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = ['foo' => 'bar'];

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
        $this->assertInstanceOf(RouteReference::class, $reference);
        $this->assertSame($resource, $reference->getRoute());
        $this->assertSame($params, $reference->getParams());
    }

    public function testGenerateNullResourceWithoutRoute()
    {
        $currentRouteName = 'my_route';
        $currentRouteParams = ['foo' => 'bar'];

        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $event = new RouteReferenceGenerationEvent(new RouteReference(null, []), $request);
        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::ROUTE_REFERENCE_GENERATION, $this->equalTo($event));

        $generator = new RouteReferenceGenerator($this->dispatcher);
        $generator->setRequestStack($requestStack);
        $reference = $generator->generate();
        $this->assertInstanceOf(RouteReference::class, $reference);
    }

    public function generateGenerator()
    {
        return [
            ['my_route', ['hello' => 'world', 'isIt' => true]],
            ['foobar', ['foo' => 'bar', 'object' => new \stdClass()]],
            [new Location(), ['switchLanguage' => 'fre-FR']],
            [new Location(), []],
        ];
    }
}
