<?php

/**
 * File containing the RoutingExtensionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\RoutingExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Test_IntegrationTestCase;

class RoutingExtensionTest extends Twig_Test_IntegrationTestCase
{
    protected function getExtensions()
    {
        return [
            new RoutingExtension($this->getRouteReferenceGenerator()),
        ];
    }

    protected function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/functions/ez_route';
    }

    protected function getRouteReferenceGenerator()
    {
        $generator = new RouteReferenceGenerator(
            $this->createMock(EventDispatcherInterface::class)
        );
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $generator->setRequestStack($requestStack);

        return $generator;
    }
}
