<?php
/**
 * File containing the UrlAliasRouter class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class UrlAliasRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    const URL_ALIAS_ROUTE_NAME = 'ez_urlalias';

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    protected $requestContext;

    /**
     * @var \Closure
     */
    protected $lazyRepository;

    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    protected $generator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Closure $lazyRepository,
        UrlAliasGenerator $generator,
        RequestContext $requestContext,
        LoggerInterface $logger = null
    )
    {
        $this->lazyRepository = $lazyRepository;
        $this->generator = $generator;
        $this->requestContext = isset( $requestContext ) ? $requestContext : new RequestContext();
        $this->logger = $logger;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        $lazyRepository = $this->lazyRepository;
        return $lazyRepository();
    }

    /**
     * Tries to match a request with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param Request $request The request to match
     *
     * @return array An array of parameters
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException If no matching resource could be found
     * @throws MethodNotAllowedException If a matching resource was found but the request method is not allowed
     */
    public function matchRequest( Request $request )
    {
        try
        {
            $urlAlias = $this->getRepository()->getURLAliasService()->lookup(
                $request->attributes->get(
                    'semanticPathinfo',
                    $request->getPathInfo()
                )
            );

            $params = array(
                '_route' => self::URL_ALIAS_ROUTE_NAME
            );
            switch ( $urlAlias->type )
            {
                case UrlAlias::LOCATION:
                    $params += array(
                        '_controller' => 'ezpublish.controller.content.view:viewLocation',
                        'locationId' => $urlAlias->destination,
                        'viewType' => ViewManager::VIEW_TYPE_FULL,
                        'layout' => true,
                    );

                    $request->attributes->set( 'locationId', $urlAlias->destination );

                    if ( $urlAlias->isHistory === true )
                    {
                        $activeUrlAlias = $this->getRepository()->getURLAliasService()->reverseLookup(
                            $this->getRepository()->getLocationService()->loadLocation(
                                $urlAlias->destination
                            )
                        );

                        $request->attributes->set( 'semanticPathinfo', $activeUrlAlias->path );
                        $request->attributes->set( 'needsRedirect', true );
                    }

                    if ( isset( $this->logger ) )
                        $this->logger->info( "UrlAlias matched location #{$urlAlias->destination}. Forwarding to ViewController" );

                    break;

                case UrlAlias::RESOURCE:
                case UrlAlias::VIRTUAL:
                    $request->attributes->set( 'semanticPathinfo', "/$urlAlias->destination" );
                    // In URLAlias terms, "forward" means "redirect".
                    if ( $urlAlias->forward )
                        $request->attributes->set( 'needsRedirect', true );
                    else
                        $request->attributes->set( 'needsForward', true );
                    break;
            }

            return $params;
        }
        catch ( NotFoundException $e )
        {
            throw new ResourceNotFoundException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    /**
     * Gets the RouteCollection instance associated with this Router.
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * Generates a URL for a location, from the given parameters.
     *
     * It is possible to directly pass a Location object as the route name, as the ChainRouter allows it through ChainedRouterInterface.
     *
     * If $name is a route name, the "location" key in $parameters must be set to a valid eZ\Publish\API\Repository\Values\Content\Location object.
     * "locationId" can also be provided.
     *
     * If the generator is not able to generate the url, it must throw the RouteNotFoundException
     * as documented below.
     *
     * @see UrlAliasRouter::supports()
     *
     * @param string|\eZ\Publish\API\Repository\Values\Content\Location $name The name of the route or a Location instance
     * @param mixed $parameters An array of parameters
     * @param boolean $absolute Whether to generate an absolute URL
     *
     * @throws \LogicException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \InvalidArgumentException
     *
     * @return string The generated URL
     *
     * @api
     */
    public function generate( $name, $parameters = array(), $absolute = false )
    {
        // Direct access to Location
        if ( $name instanceof Location )
        {
            return $this->generator->generate( $name, $parameters, $absolute );
        }

        // Normal route name
        if ( $name === self::URL_ALIAS_ROUTE_NAME )
        {
            // We must have at least 'location' or 'locationId' to retrieve the UrlAlias
            if ( !isset( $parameters['location'] ) && !isset( $parameters['locationId'] ) )
            {
                throw new \InvalidArgumentException(
                    "When generating an UrlAlias route, either 'location' or 'locationId must be provided."
                );
            }

            // Check if location is a valid Location object
            if ( isset( $parameters['location'] ) && !$parameters['location'] instanceof Location )
            {
                throw new \LogicException(
                    "When generating an UrlAlias route, 'location' parameter must be a valid eZ\\Publish\\API\\Repository\\Values\\Content\\Location."
                );
            }

            $location = isset( $parameters['location'] ) ? $parameters['location'] : $this->getRepository()->getLocationService()->loadLocation( $parameters['locationId'] );
            unset( $parameters['location'], $parameters['locationId'] );
            return $this->generator->generate( $location, $parameters, $absolute );
        }

        throw new RouteNotFoundException( 'Could not match route' );
    }

    public function setContext( RequestContext $context )
    {
        $this->requestContext = $context;
    }

    public function getContext()
    {
        return $this->requestContext;
    }

    /**
     * Not supported. Please use matchRequest() instead.
     *
     * @param $pathinfo
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function match( $pathinfo )
    {
        throw new \RuntimeException( "The UrlAliasRouter doesn't support the match() method. Please use matchRequest() instead." );
    }

    /**
     * Whether the router supports the thing in $name to generate a route.
     *
     * This check does not need to look if the specific instance can be
     * resolved to a route, only whether the router can generate routes from
     * objects of this class.
     * @param mixed $name The route name or route object
     *
     * @return boolean
     */
    public function supports( $name )
    {
        return $name instanceof Location || $name === self::URL_ALIAS_ROUTE_NAME;
    }
}
