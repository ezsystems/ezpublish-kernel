<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\URLWildcardService;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
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
    public const URL_ALIAS_ROUTE_NAME = 'ez_urlalias';

    /** @var \eZ\Publish\API\Repository\URLWildcardService */
    private $wildcardService;

    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator */
    private $generator;

    /** @var \Symfony\Component\Routing\RequestContext */
    private $requestContext;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \eZ\Publish\API\Repository\URLWildcardService $wildcardService
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator $generator
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     */
    public function __construct(
        URLWildcardService $wildcardService,
        UrlAliasGenerator $generator,
        RequestContext $requestContext
    ) {
        $this->wildcardService = $wildcardService;
        $this->generator = $generator;
        $this->requestContext = $requestContext;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array An array of parameters
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function matchRequest(Request $request): array
    {
        $requestedPath = $request->attributes->get('semanticPathinfo', $request->getPathInfo());

        try {
            $urlWildcardTranslationResult = $this->wildcardService->translate($requestedPath);
        } catch (NotFoundException $e) {
            throw new ResourceNotFoundException($e->getMessage(), $e->getCode(), $e);
        }

        if ($this->logger !== null) {
            $this->logger->info("UrlWildcard matched. Destination URL: {$urlWildcardTranslationResult->uri}");
        }

        // set translated path for the next router
        $request->attributes->set('semanticPathinfo', $urlWildcardTranslationResult->uri);
        $request->attributes->set('needsRedirect', (bool) $urlWildcardTranslationResult->forward);

        // and throw Exception to pass processing to the next router
        throw new ResourceNotFoundException();
    }

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection();
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param int $referenceType
     *
     * @return string|void
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        throw new RouteNotFoundException('Could not match route');
    }

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     */
    public function setContext(RequestContext $context): void
    {
        $this->requestContext = $context;
        $this->generator->setRequestContext($context);
    }

    /**
     * @return \Symfony\Component\Routing\RequestContext
     */
    public function getContext(): RequestContext
    {
        return $this->requestContext;
    }

    /**
     * Not supported. Please use matchRequest() instead.
     *
     * @param string $pathinfo
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function match($pathinfo): array
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
    public function supports($name): bool
    {
        return $name === self::URL_ALIAS_ROUTE_NAME;
    }

    /**
     * @see Symfony\Cmf\Component\Routing\VersatileGeneratorInterface::getRouteDebugMessage()
     */
    public function getRouteDebugMessage($name, array $parameters = []): string
    {
        if ($name instanceof RouteObjectInterface) {
            return 'Route with key ' . $name->getRouteKey();
        }

        if ($name instanceof SymfonyRoute) {
            return 'Route with pattern ' . $name->getPath();
        }

        return $name;
    }
}
