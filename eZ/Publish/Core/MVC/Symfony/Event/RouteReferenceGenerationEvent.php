<?php

/**
 * File containing the RouteReferenceGenerationEvent class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Event;

use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Event dispatched when generating a RouteReference.
 */
class RouteReferenceGenerationEvent extends Event
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\RouteReference */
    private $routeReference;

    /** @var \Symfony\Component\HttpFoundation\Request */
    private $request;

    public function __construct(RouteReference $routeReference, Request $request)
    {
        $this->routeReference = $routeReference;
        $this->request = $request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\RouteReference
     */
    public function getRouteReference()
    {
        return $this->routeReference;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\RouteReference $routeReference
     */
    public function setRouteReference($routeReference)
    {
        $this->routeReference = $routeReference;
    }
}
