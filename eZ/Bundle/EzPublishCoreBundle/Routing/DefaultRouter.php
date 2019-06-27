<?php

/**
 * File containing the DefaultRouter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Routing;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Extension of Symfony default router implementing RequestMatcherInterface.
 */
class DefaultRouter extends Router implements RequestMatcherInterface, SiteAccessAware
{
    /** @var SiteAccess */
    protected $siteAccess;

    protected $nonSiteAccessAwareRoutes = [];

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface */
    protected $siteAccessRouter;

    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
    }

    /**
     * Injects route names that are not supposed to be SiteAccess aware.
     * i.e. Routes pointing to asset generation (like assetic).
     *
     * @param array $routes
     */
    public function setNonSiteAccessAwareRoutes(array $routes)
    {
        $this->nonSiteAccessAwareRoutes = $routes;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface $siteAccessRouter
     */
    public function setSiteAccessRouter(SiteAccessRouterInterface $siteAccessRouter)
    {
        $this->siteAccessRouter = $siteAccessRouter;
    }

    public function matchRequest(Request $request)
    {
        return $this->match($request->attributes->get('semanticPathinfo', $request->getPathInfo()));
    }

    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $siteAccess = $this->siteAccess;
        $originalContext = $context = $this->getContext();
        $isSiteAccessAware = $this->isSiteAccessAwareRoute($name);

        // Retrieving the appropriate SiteAccess to generate the link for.
        if (isset($parameters['siteaccess']) && $isSiteAccessAware) {
            $siteAccess = $this->siteAccessRouter->matchByName($parameters['siteaccess']);
            if ($siteAccess instanceof SiteAccess && $siteAccess->matcher instanceof SiteAccess\VersatileMatcher) {
                // Switch request context for link generation.
                $context = $this->getContextBySimplifiedRequest($siteAccess->matcher->getRequest());
                $this->setContext($context);
            } elseif ($this->logger) {
                $siteAccess = $this->siteAccess;
                $this->logger->notice("Could not generate a link using provided 'siteaccess' parameter: {$parameters['siteaccess']}. Generating using current context.");
            }

            unset($parameters['siteaccess']);
        }

        try {
            $url = parent::generate($name, $parameters, $referenceType);
        } catch (RouteNotFoundException $e) {
            // Switch back to original context, for next links generation.
            $this->setContext($originalContext);
            throw $e;
        }

        // Now putting back SiteAccess URI if needed.
        if ($isSiteAccessAware && $siteAccess && $siteAccess->matcher instanceof URILexer) {
            if ($referenceType === self::ABSOLUTE_URL || $referenceType === self::NETWORK_PATH) {
                $scheme = $context->getScheme();
                $port = '';
                if ($scheme === 'http' && $this->context->getHttpPort() != 80) {
                    $port = ':' . $this->context->getHttpPort();
                } elseif ($scheme === 'https' && $this->context->getHttpsPort() != 443) {
                    $port = ':' . $this->context->getHttpsPort();
                }

                $base = $context->getHost() . $port . $context->getBaseUrl();
            } else {
                $base = $context->getBaseUrl();
            }

            $linkUri = $base ? substr($url, strpos($url, $base) + strlen($base)) : $url;
            $url = str_replace($linkUri, $siteAccess->matcher->analyseLink($linkUri), $url);
        }

        // Switch back to original context, for next links generation.
        $this->setContext($originalContext);

        return $url;
    }

    /**
     * Checks if $routeName is a siteAccess aware route, and thus needs to have siteAccess URI prepended.
     * Will be used for link generation, only in the case of URI SiteAccess matching.
     *
     * @param $routeName
     *
     * @return bool
     */
    protected function isSiteAccessAwareRoute($routeName)
    {
        foreach ($this->nonSiteAccessAwareRoutes as $ignoredPrefix) {
            if (strpos($routeName, $ignoredPrefix) === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Merges context from $simplifiedRequest into a clone of the current context.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $simplifiedRequest
     *
     * @return \Symfony\Component\Routing\RequestContext
     */
    public function getContextBySimplifiedRequest(SimplifiedRequest $simplifiedRequest)
    {
        $context = clone $this->context;
        if ($simplifiedRequest->scheme) {
            $context->setScheme($simplifiedRequest->scheme);
        }

        if ($simplifiedRequest->port) {
            switch ($simplifiedRequest->scheme) {
                case 'https':
                    $context->setHttpsPort($simplifiedRequest->port);
                    break;
                default:
                    $context->setHttpPort($simplifiedRequest->port);
                    break;
            }
        }

        if ($simplifiedRequest->host) {
            $context->setHost($simplifiedRequest->host);
        }

        if ($simplifiedRequest->pathinfo) {
            $context->setPathInfo($simplifiedRequest->pathinfo);
        }

        return $context;
    }
}
