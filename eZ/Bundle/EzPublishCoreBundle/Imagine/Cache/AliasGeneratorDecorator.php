<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Cache;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\SPI\Variation\VariationHandler;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Persistence Cache layer for AliasGenerator.
 */
class AliasGeneratorDecorator implements VariationHandler, SiteAccessAware
{
    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $aliasGenerator;

    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    private $cache;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    /** @var \Symfony\Component\Routing\RequestContext */
    private $requestContext;

    /**
     * @param \eZ\Publish\SPI\Variation\VariationHandler $aliasGenerator
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cache
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     */
    public function __construct(VariationHandler $aliasGenerator, TagAwareAdapterInterface $cache, RequestContext $requestContext)
    {
        $this->aliasGenerator = $aliasGenerator;
        $this->cache = $cache;
        $this->requestContext = $requestContext;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     * @param array $parameters
     *
     * @return \eZ\Publish\SPI\Variation\Values\Variation
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = [])
    {
        $item = $this->cache->getItem($this->getCacheKey($field, $versionInfo, $variationName));
        $image = $item->get();
        if (!$item->isHit()) {
            $image = $this->aliasGenerator->getVariation($field, $versionInfo, $variationName, $parameters);
            $item->set($image);
            $item->tag($this->getTagsForVariation($field, $versionInfo, $variationName));
            $this->cache->save($item);
        }

        return $image;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     */
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     *
     * @return string
     */
    private function getCacheKey(Field $field, VersionInfo $versionInfo, $variationName)
    {
        return sprintf(
            'ez-image-variation-%s-%s-%s-%d-%d-%d-%s-%s',
            $this->siteAccess ? $this->siteAccess->name : 'default',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $this->requestContext->getScheme() === 'https' ? $this->requestContext->getHttpsPort() : $this->requestContext->getHttpPort(),
            $versionInfo->getContentInfo()->id,
            $versionInfo->id,
            $field->id,
            $variationName
        );
    }

    private function getTagsForVariation(Field $field, VersionInfo $versionInfo, string $variationName): array
    {
        $contentId = $versionInfo->getContentInfo()->id;

        return [
            'image-variation',
            'image-variation-name-' . $variationName,
            'image-variation-siteaccess-' . ($this->siteAccess ? $this->siteAccess->name : 'default'),
            'image-variation-content-' . $contentId,
            'image-variation-field-' . $field->id,
            'content-' . $contentId,
            'content-' . $contentId . '-version-' . $versionInfo->versionNo,
        ];
    }
}
