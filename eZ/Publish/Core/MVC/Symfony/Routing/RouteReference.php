<?php

/**
 * File containing the RouteReference class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing;

use Symfony\Component\HttpFoundation\ParameterBag;

class RouteReference
{
    /** @var \Symfony\Component\HttpFoundation\ParameterBag */
    private $params;

    /** @var mixed Route name or resource (e.g. Location object). */
    private $route;

    public function __construct($route, array $params = [])
    {
        $this->route = $route;
        $this->params = new ParameterBag($params);
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params->all();
    }

    /**
     * Sets a route parameter.
     *
     * @param string $parameterName
     * @param mixed $value
     */
    public function set($parameterName, $value)
    {
        $this->params->set($parameterName, $value);
    }

    /**
     * Returns a route parameter.
     *
     * @param string $parameterName
     * @param mixed $defaultValue
     * @param bool $deep
     *
     * @return mixed
     */
    public function get($parameterName, $defaultValue = null, $deep = false)
    {
        return $this->params->get($parameterName, $defaultValue, $deep);
    }

    public function has($parameterName)
    {
        return $this->params->has($parameterName);
    }

    /**
     * Removes a route parameter.
     *
     * @param string $parameterName
     */
    public function remove($parameterName)
    {
        $this->params->remove($parameterName);
    }
}
