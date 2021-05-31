<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Routing\JsRouting;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Decorator of FOSJsRouting routes extractor.
 * Ensures that base URL contains the SiteAccess part when applicable.
 *
 * @internal
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

    public function getRoutes()
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
    public function getBaseUrl()
    {
        $baseUrl = $this->innerExtractor->getBaseUrl();
        $siteAccess = $this->requestStack->getMasterRequest()->attributes->get('siteaccess');
        if ($siteAccess instanceof SiteAccess && $siteAccess->matcher instanceof SiteAccess\URILexer) {
            $baseUrl .= $siteAccess->matcher->analyseLink('');
        }

        return $baseUrl;
    }

    public function getPrefix($locale)
    {
        return $this->innerExtractor->getPrefix($locale);
    }

    public function getHost()
    {
        return $this->innerExtractor->getHost();
    }

    public function getScheme()
    {
        return $this->innerExtractor->getScheme();
    }

    public function getCachePath($locale)
    {
        return $this->innerExtractor->getCachePath($locale);
    }

    public function getResources()
    {
        return $this->innerExtractor->getResources();
    }

    public function getExposedRoutes()
    {
        return $this->innerExtractor->getExposedRoutes();
    }
}
