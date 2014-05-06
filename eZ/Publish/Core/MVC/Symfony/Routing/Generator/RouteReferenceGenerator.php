<?php
/**
 * File containing the RouteReferenceGenerator class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing\Generator;

use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\RouteReference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class RouteReferenceGenerator implements RouteReferenceGeneratorInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct( EventDispatcherInterface $dispatcher )
    {
        $this->dispatcher = $dispatcher;
    }

    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    /**
     * Generates a RouteReference, based on the given resource and associated params.
     * If $resource is null, generated route reference will be based on current request's route and parameters.
     *
     * @param mixed $resource The route name. Can be any resource supported by the different routers (e.g. Location object).
     * @param array $params Array of parameters, used to generate the final link along with $resource.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\RouteReference
     */
    public function generate( $resource = null, array $params = array() )
    {
        if ( $resource === null )
        {
            $resource = $this->request->attributes->get( '_route' );
            $params += $this->request->attributes->get( '_route_params' );
        }

        $event = new RouteReferenceGenerationEvent( new RouteReference( $resource, $params ), $this->request );
        $this->dispatcher->dispatch( MVCEvents::ROUTE_REFERENCE_GENERATION, $event );
        return $event->getRouteReference();
    }
}
