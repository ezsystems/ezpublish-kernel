<?php

/**
 * File containing the RoutingExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class RoutingExtension extends Twig_Extension
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface */
    private $routeReferenceGenerator;

    public function __construct(RouteReferenceGeneratorInterface $routeReferenceGenerator)
    {
        $this->routeReferenceGenerator = $routeReferenceGenerator;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'ez_route',
                [$this, 'getRouteReference']
            ),
        ];
    }

    public function getName()
    {
        return 'ezpublish.routing';
    }

    /**
     * @param mixed $resource
     * @param array $params
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\RouteReference
     */
    public function getRouteReference($resource = null, $params = [])
    {
        return $this->routeReferenceGenerator->generate($resource, $params);
    }
}
