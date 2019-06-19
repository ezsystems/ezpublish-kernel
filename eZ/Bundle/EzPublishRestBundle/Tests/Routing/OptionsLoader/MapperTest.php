<?php

/**
 * File containing the MapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Routing\OptionsLoader;

use eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader\Mapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;

class MapperTest extends TestCase
{
    /** @var Mapper */
    protected $mapper;

    public function setUp()
    {
        $this->mapper = new Mapper();
    }

    public function testGetOptionsRouteName()
    {
        $route = new Route('/route/{id}');

        self::assertEquals(
            'ezpublish_rest_options_route_{id}',
            $this->mapper->getOptionsRouteName($route)
        );
    }

    public function testMergeMethodsDefault()
    {
        $optionsRoute = new Route('', ['allowedMethods' => 'PUT,DELETE']);
        $restRoute = new Route('', [], [], [], '', [], ['GET', 'POST']);

        $mergedOptionsRoute = $this->mapper->mergeMethodsDefault($optionsRoute, $restRoute);
        self::assertEquals(
            'PUT,DELETE,GET,POST',
            $mergedOptionsRoute->getDefault('allowedMethods')
        );
        self::assertEquals(
            $optionsRoute->getMethods(),
            $mergedOptionsRoute->getMethods()
        );
    }

    public function testMapRoute()
    {
        $restRoute = new Route(
            '/route/one/{id}',
            ['_controller' => 'anything'],
            ['id' => '[0-9]+'],
            [],
            '',
            [],
            ['PUT', 'DELETE']
        );

        $optionsRoute = $this->mapper->mapRoute($restRoute);

        self::assertEquals(
            ['OPTIONS'],
            $optionsRoute->getMethods()
        );

        self::assertEquals(
            $restRoute->getRequirement('id'),
            $optionsRoute->getRequirement('id')
        );

        self::assertEquals(
            'PUT,DELETE',
            $optionsRoute->getDefault('allowedMethods')
        );

        self::assertEquals(
            'ezpublish_rest.controller.options:getRouteOptions',
            $optionsRoute->getDefault('_controller')
        );
    }
}
