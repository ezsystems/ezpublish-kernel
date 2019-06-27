<?php

/**
 * File containing the Generator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Routing;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Base class for eZ Publish Url generation.
 */
abstract class Generator implements SiteAccessAware
{
    /** @var \Symfony\Component\Routing\RequestContext */
    protected $requestContext;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface */
    protected $siteAccessRouter;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    protected $siteAccess;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     */
    public function setRequestContext(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface $siteAccessRouter
     */
    public function setSiteAccessRouter(SiteAccessRouterInterface $siteAccessRouter)
    {
        $this->siteAccessRouter = $siteAccessRouter;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     */
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Triggers URL generation for $urlResource and $parameters.
     *
     * @param mixed $urlResource Type can be anything, depending on the context. It's up to the router to pass the appropriate value to the implementor.
     * @param array $parameters Arbitrary hash of parameters to generate a link.
     *                          SiteAccess name can be provided as 'siteaccess' to generate a link to it (cross siteaccess link).
     * @param int $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string
     */
    public function generate($urlResource, array $parameters, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $siteAccess = $this->siteAccess;
        $requestContext = $this->requestContext;

        // Retrieving the appropriate SiteAccess to generate the link for.
        if (isset($parameters['siteaccess'])) {
            $siteAccess = $this->siteAccessRouter->matchByName($parameters['siteaccess']);
            if ($siteAccess instanceof SiteAccess && $siteAccess->matcher instanceof SiteAccess\VersatileMatcher) {
                $requestContext = $this->getContextBySimplifiedRequest($siteAccess->matcher->getRequest());
            } elseif ($this->logger) {
                $siteAccess = $this->siteAccess;
                $this->logger->notice("Could not generate a link using provided 'siteaccess' parameter: {$parameters['siteaccess']}. Generating using current context.");
                unset($parameters['siteaccess']);
            }
        }

        $url = $this->doGenerate($urlResource, $parameters);

        // Add the SiteAccess URI back if needed.
        if ($siteAccess && $siteAccess->matcher instanceof SiteAccess\URILexer) {
            $url = $siteAccess->matcher->analyseLink($url);
        }

        $url = $requestContext->getBaseUrl() . $url;

        if ($referenceType === UrlGeneratorInterface::ABSOLUTE_URL) {
            $url = $this->generateAbsoluteUrl($url, $requestContext);
        }

        return $url;
    }

    /**
     * Generates the URL from $urlResource and $parameters.
     *
     * @param mixed $urlResource
     * @param array $parameters
     *
     * @return string
     */
    abstract public function doGenerate($urlResource, array $parameters);

    /**
     * Generates an absolute URL from $uri and the request context.
     *
     * @param string $uri
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     *
     * @return string
     */
    protected function generateAbsoluteUrl($uri, RequestContext $requestContext)
    {
        $scheme = $requestContext->getScheme();
        $port = '';
        if ($scheme === 'http' && $requestContext->getHttpPort() != 80) {
            $port = ':' . $requestContext->getHttpPort();
        } elseif ($scheme === 'https' && $requestContext->getHttpsPort() != 443) {
            $port = ':' . $requestContext->getHttpsPort();
        }

        return $scheme . '://' . $requestContext->getHost() . $port . $uri;
    }

    /**
     * Merges context from $simplifiedRequest into a clone of the current context.
     *
     * @param SimplifiedRequest $simplifiedRequest
     *
     * @return RequestContext
     */
    private function getContextBySimplifiedRequest(SimplifiedRequest $simplifiedRequest)
    {
        $context = clone $this->requestContext;
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
