<?php

/**
 * File containing the RouteReferenceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing\Tests;

use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use PHPUnit\Framework\TestCase;

class RouteReferenceTest extends TestCase
{
    public function testConstruct()
    {
        $route = 'my_route';
        $params = ['foo' => 'bar', 'some' => 'thing'];
        $reference = new RouteReference($route, $params);
        $this->assertSame($route, $reference->getRoute());
        $this->assertSame($params, $reference->getParams());
    }

    public function testGetSetRoute()
    {
        $initialRoute = 'foo';
        $newRoute = 'bar';

        $reference = new RouteReference($initialRoute);
        $this->assertSame($initialRoute, $reference->getRoute());
        $reference->setRoute($newRoute);
        $this->assertSame($newRoute, $reference->getRoute());
    }

    public function testGetSetParams()
    {
        $reference = new RouteReference('foo');
        $this->assertSame([], $reference->getParams());

        $reference->set('foo', 'bar');
        $this->assertSame('bar', $reference->get('foo'));
        $obj = new \stdClass();
        $reference->set('object', $obj);
        $this->assertSame($obj, $reference->get('object'));
        $reference->set('bool', true);
        $this->assertTrue($reference->get('bool'));
        $this->assertSame(
            ['foo' => 'bar', 'object' => $obj, 'bool' => true],
            $reference->getParams()
        );

        $defaultValue = 'http://www.phoenix-rises.fm';
        $this->assertSame($defaultValue, $reference->get('url', $defaultValue));
    }

    public function testRemoveParam()
    {
        $reference = new RouteReference('foo');
        $reference->set('foo', 'bar');
        $this->assertTrue($reference->has('foo'));
        $this->assertSame('bar', $reference->get('foo'));

        $reference->remove('foo');
        $this->assertFalse($reference->has('foo'));
    }
}
