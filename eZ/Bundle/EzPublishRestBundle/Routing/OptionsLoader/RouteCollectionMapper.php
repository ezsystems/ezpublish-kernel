<?php

/**
 * File containing the OptionsRouteCollection class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Routing\OptionsLoader;

use Symfony\Component\Routing\RouteCollection;

/**
 * Maps a REST routes collection to the corresponding set of REST OPTIONS routes.
 *
 * Merges routes with the same path to a unique one, with the aggregate of merged methods in the _methods default.
 */
class RouteCollectionMapper
{
    /** @var Mapper */
    protected $mapper;

    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Iterates over $restRouteCollection, and returns the corresponding RouteCollection of OPTIONS REST routes.
     *
     * @param RouteCollection $restRouteCollection
     *
     * @return RouteCollection
     */
    public function mapCollection(RouteCollection $restRouteCollection)
    {
        $optionsRouteCollection = new RouteCollection();

        foreach ($restRouteCollection->all() as $restRoute) {
            $optionsRouteName = $this->mapper->getOptionsRouteName($restRoute);

            $optionsRoute = $optionsRouteCollection->get($optionsRouteName);
            if ($optionsRoute === null) {
                $optionsRoute = $this->mapper->mapRoute($restRoute);
            } else {
                $optionsRoute = $this->mapper->mergeMethodsDefault($optionsRoute, $restRoute);
            }

            $optionsRouteCollection->add($optionsRouteName, $optionsRoute);
        }

        return $optionsRouteCollection;
    }
}
