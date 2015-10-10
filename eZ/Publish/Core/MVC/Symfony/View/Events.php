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
     * Listeners receive a ViewBuilderParameterCollectionEvent, that gives access to the Request object.
     * The default listener will add all the request attributes. Extra listeners could for instance add custom builder
     * attributes based on the request headers.
     */
    const BUILDER_PARAMETER_COLLECTION = 'view.builder_parameter_collection';

    /**
     * Dispatched before ViewParameters are injected into a View to collect the view parameters.
     *
     * Receives a ViewParameterFilterEvent, that gives access to a copy of the View as well as the builder input parameters.
     * Listeners can modify the parameters that will be injected as view parameters, and made available in controller
     * and templates.
     */
    const VIEW_PARAMETERS_INJECTION = 'view.parameters_injection';
}
