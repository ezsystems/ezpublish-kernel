<?php

/**
 * File containing the MapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Routing\OptionsLoader;

use eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader\Mapper;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Routing\Route;

class MapperTest extends PHPUnit_Framework_TestCase
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
        $optionsRoute = new Route('', array('allowedMethods' => 'PUT,DELETE'));
        $restRoute = new Route('', array(), array(), array(), '', array(), array('GET', 'POST'));

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
            array('_controller' => 'anything'),
            array('id' => '[0-9]+'),
            array(),
            '',
            array(),
            array('PUT', 'DELETE')
        );

        $optionsRoute = $this->mapper->mapRoute($restRoute);

        self::assertEquals(
            array('OPTIONS'),
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
            '_ezpublish_rest.controller.options:getRouteOptions',
            $optionsRoute->getDefault('_controller')
        );
    }
}
