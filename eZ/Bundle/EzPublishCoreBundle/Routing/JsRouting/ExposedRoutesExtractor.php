<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Routing\JsRouting;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Decorator of FOSJsRouting routes extractor.
 * Ensures that base URL contains the SiteAccess part when applicable.
 */
class ExposedRoutesExtractor implements ExposedRoutesExtractorInterface
{
    /** @var ExposedRoutesExtractorInterface */
    private $innerExtractor;

    /** @var Request */
    private $masterRequest;

    public function __construct(ExposedRoutesExtractorInterface $innerExtractor, Request $masterRequest)
    {
        $this->innerExtractor = $innerExtractor;
        $this->masterRequest = $masterRequest;
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
        $siteAccess = $this->masterRequest->attributes->get('siteaccess');
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
