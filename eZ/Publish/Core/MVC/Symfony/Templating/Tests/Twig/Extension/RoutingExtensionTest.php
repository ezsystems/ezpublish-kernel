<?php
/**
 * File containing the RoutingExtensionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpFoundation\Request;
use Twig_Test_IntegrationTestCase;

class RoutingExtensionTest extends Twig_Test_IntegrationTestCase
{
    protected function getExtensions()
    {
        return array(
            new RoutingExtension( $this->getRouteReferenceGenerator() )
        );
    }

    protected function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/functions/ez_route';
    }

    protected function getRouteReferenceGenerator()
    {
        $generator = new RouteReferenceGenerator(
            $this->getMock( 'Symfony\Component\EventDispatcher\EventDispatcherInterface' )
        );
        $generator->setRequest( new Request() );

        return $generator;
    }
}
