<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Routing\JsRouting;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Decorator of FOSJsRouting routes extractor.
 * Ensures that base URL contains the SiteAccess part when applicable.
 */
class ExposedRoutesExtractor implements ExposedRoutesExtractorInterface
{
    /** @var \FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface */
    private $innerExtractor;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    public function __construct(ExposedRoutesExtractorInterface $innerExtractor, RequestStack $requestStack)
    {
        $this->innerExtractor = $innerExtractor;
        $this->requestStack = $requestStack;
    }

    public function getRoutes(): RouteCollection
    {
        return $this->innerExtractor->getRoutes();
    }

    /**
     * {@inheritdoc}
     *
     * Will add the SiteAccess if configured in the URI.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        $baseUrl = $this->innerExtractor->getBaseUrl();
        $siteAccess = $masterRequest->attributes->get('siteaccess');
        if ($siteAccess instanceof SiteAccess && $siteAccess->matcher instanceof SiteAccess\URILexer) {
            $baseUrl .= $siteAccess->matcher->analyseLink('');
        }

        return $baseUrl;
    }

    public function getPrefix($locale): string
    {
        return $this->innerExtractor->getPrefix($locale);
    }

    public function getHost(): string
    {
        return $this->innerExtractor->getHost();
    }

    public function getScheme(): string
    {
        return $this->innerExtractor->getScheme();
    }

    public function getCachePath($locale): string
    {
        return $this->innerExtractor->getCachePath($locale);
    }

    public function getResources(): array
    {
        return $this->innerExtractor->getResources();
    }

    public function getPort(): string
    {
        return $this->innerExtractor->getPort();
    }

    public function isRouteExposed(Route $route, $name): bool
    {
        return $this->innerExtractor->isRouteExposed($route, $name);
    }
}
