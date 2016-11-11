<?php

/**
 * File containing the Loader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader\RouteCollectionMapper;
use Symfony\Component\Routing\RouteCollection;

/**
 * Goes through all REST routes, and registers new routes for all routes
 * a new one with the OPTIONS method.
 */
class OptionsLoader extends Loader
{
    /** @var RouteCollectionMapperMapper */
    protected $routeCollectionMapper;

    public function __construct(RouteCollectionMapper $mapper)
    {
        $this->routeCollectionMapper = $mapper;
    }

    /**
     * @param mixed $resource
     * @param string $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        return $this->routeCollectionMapper->mapCollection($this->import($resource));
    }

    public function supports($resource, $type = null)
    {
        return $type === 'rest_options';
    }
}
