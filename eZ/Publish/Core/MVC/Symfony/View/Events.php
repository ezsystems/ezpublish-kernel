<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

/**
 * Events constants of views.
 */
class Events
{
    /**
     * Dispatched before the ViewBuilder is called to collect the ViewBuilder parameters.
     *
     * Listeners receive a FilterViewBuilderParametersEvent, that gives access to the Request object.
     * The default listener will add all the request attributes. Extra listeners could for instance add custom builder
     * attributes based on the request headers.
     */
    const FILTER_BUILDER_PARAMETERS = 'view.builder_parameter_collection';
}
