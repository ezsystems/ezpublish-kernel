<?php
/**
 * File containing the UrlAliasRouter class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing;

use eZ\Publish\API\Repository\Repository,
    eZ\Publish\API\Repository\Values\Content\URLAlias,
    eZ\Publish\API\Repository\Exceptions\NotFoundException,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager,
    eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator,
    eZ\Publish\Core\MVC\ConfigResolverInterface,
    Symfony\Component\Routing\RouterInterface,
    Symfony\Component\Routing\Matcher\RequestMatcherInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Routing\RequestContext,
    Symfony\Component\HttpKernel\Log\LoggerInterface,
    Symfony\Component\Routing\RouteCollection,
    Symfony\Component\Routing\Exception\RouteNotFoundException,
    Symfony\Component\Routing\Exception\ResourceNotFoundException;

class UrlAliasRouter implements RouterInterface, RequestMatcherInterface
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
     * @var array
     */
    protected $prioritizedLanguages;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Closure $lazyRepository,
        UrlAliasGenerator $generator,
        array $prioritizedLanguages,
        RequestContext $requestContext,
        LoggerInterface $logger = null
    )
    {
        $this->lazyRepository = $lazyRepository;
        $this->generator = $generator;
        $this->prioritizedLanguages = $prioritizedLanguages;
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
     * Returns the locale code having the top priority.
     *
     * @return string
     */
    protected function getTopLanguage()
    {
        return !empty( $this->prioritizedLanguages ) ? $this->prioritizedLanguages[0] : 'eng-GB';
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
     * @throws ResourceNotFoundException If no matching resource could be found
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
                ),
                $this->getTopLanguage()
            );

            $params = array(
                '_route' => self::URL_ALIAS_ROUTE_NAME
            );
            switch ( $urlAlias->type )
            {
                case UrlAlias::LOCATION:
                    $params += array(
                        '_controller'   => 'ezpublish.controller.content.view:viewLocation',
                        'locationId'    => $urlAlias->destination->id,
                        'viewType'      => ViewManager::VIEW_TYPE_FULL
                    );

                    $request->attributes->set( 'locationId', $urlAlias->destination->id );

                    if ( isset( $this->logger ) )
                        $this->logger->info( "UrlAlias matched location #{$urlAlias->destination->id}. Forwarding to ViewController" );

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

        throw new ResourceNotFoundException( "Could not match UrlAlias" );
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
     * If applicable, the "location" key in $parameters must be set to a valid eZ\Publish\API\Repository\Values\Content\Location object.
     * "locationId" can also be provided.
     *
     * If the generator is not able to generate the url, it must throw the RouteNotFoundException
     * as documented below.
     *
     * @param string  $name       The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     *
     * @throws \LogicException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \InvalidArgumentException
     * @return string The generated URL
     *
     * @api
     */
    public function generate( $name, $parameters = array(), $absolute = false )
    {
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
     * @return void
     * @throws \RuntimeException
     */
    public function match( $pathinfo )
    {
        throw new \RuntimeException( "The UrlAliasRouter doesn't support the match() method. Please use matchRequest() instead." );
    }
}
