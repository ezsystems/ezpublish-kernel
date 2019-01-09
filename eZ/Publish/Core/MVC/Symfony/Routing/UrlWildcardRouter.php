<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route as SymfonyRoute;

class UrlWildcardRouter implements ChainedRouterInterface, RequestMatcherInterface
{
    const URL_ALIAS_ROUTE_NAME = 'ez_urlalias';

    /**
     * @var \eZ\Publish\API\Repository\URLWildcardService
     */
    private $wildcardService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    private $generator;

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    private $requestContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \eZ\Publish\API\Repository\URLWildcardService $wildcardService
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator $generator
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        URLWildcardService $wildcardService,
        UrlAliasGenerator $generator,
        RequestContext $requestContext,
        LoggerInterface $logger
    ) {
        $this->wildcardService = $wildcardService;
        $this->generator = $generator;
        $this->requestContext = $requestContext;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     *
     * @return array An array of parameters
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function matchRequest(Request $request)
    {
        $requestedPath = $request->attributes->get('semanticPathinfo', $request->getPathInfo());

        $path = '';
        $redirect = false;
        $params = [];

        try {
            /** @var UrlWildcard $urlWildcard */
            $urlWildcard = $this->getUrlWildcard($requestedPath);
            $path = $urlWildcard->destinationUrl;
            $redirect = $urlWildcard->forward;
        } catch (NotFoundException $e) {
            try {
                /** @var URLWildcardTranslationResult $urlWildcardTranslationResult */
                $urlWildcardTranslationResult = $this->getUrlWildcardTranslationResult($requestedPath);
                $path = $urlWildcardTranslationResult->uri;
                $redirect = $urlWildcardTranslationResult->forward;
            } catch (NotFoundException $e) {
                throw new ResourceNotFoundException($e->getMessage(), $e->getCode(), $e);
            }
        }

        // redirect if Url Wildcard set as forward
        if ($redirect) {
            $params = [
                '_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction',
                'path' => $path,
                'keepRequestMethod' => true,
            ];

            return $params;
        }

        // set translated path for the next router
        $request->attributes->set('semanticPathinfo', $path);
        $request->attributes->set('needsRedirect', false);

        // and throw Exception to pass processing to the next router
        throw new ResourceNotFoundException();
    }

    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        throw new RouteNotFoundException('Could not match route');
    }

    public function setContext(RequestContext $context)
    {
        $this->requestContext = $context;
        $this->generator->setRequestContext($context);
    }

    public function getContext()
    {
        $this->requestContext;
    }

    /**
     * Not supported. Please use matchRequest() instead.
     *
     * @param $pathinfo
     *
     * @throws \RuntimeException
     */
    public function match($pathinfo)
    {
        throw new \RuntimeException("The UrlWildcardRouter doesn't support the match() method. Please use matchRequest() instead.");
    }

    /**
     * Whether the router supports the thing in $name to generate a route.
     *
     * This check does not need to look if the specific instance can be
     * resolved to a route, only whether the router can generate routes from
     * objects of this class.
     *
     * @param mixed $name The route name or route object
     *
     * @return bool
     */
    public function supports($name)
    {
        return $name === self::URL_ALIAS_ROUTE_NAME;
    }

    /**
     * @see Symfony\Cmf\Component\Routing\VersatileGeneratorInterface::getRouteDebugMessage()
     */
    public function getRouteDebugMessage($name, array $parameters = array())
    {
        if ($name instanceof RouteObjectInterface) {
            return 'Route with key ' . $name->getRouteKey();
        }

        if ($name instanceof SymfonyRoute) {
            return 'Route with pattern ' . $name->getPath();
        }

        return $name;
    }

    /**
     * Returns the UrlWildcard object.
     *
     * @param string $requestedPath
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     *
     * @throws NotFoundException
     */
    protected function getUrlWildcard($requestedPath)
    {
        return $this->wildcardService->lookup($requestedPath);
    }

    /**
     * Returns the UrlWildcardTranslationResult object.
     *
     * @param $requestedPath
     *
     * @return URLWildcardTranslationResult
     *
     * @throws NotFoundException
     */
    protected function getUrlWildcardTranslationResult($requestedPath)
    {
        return $this->wildcardService->translate($requestedPath);
    }
}
