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
use eZ\Publish\Core\Persistence\Cache\Tags\TagGeneratorInterface;
use eZ\Publish\SPI\Variation\VariationHandler;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Persistence Cache layer for AliasGenerator.
 */
class AliasGeneratorDecorator implements VariationHandler, SiteAccessAware
{
    private const PREFIXED_IMAGE_VARIATION_TAG = 'prefixed_image_variation';
    private const IMAGE_VARIATION_TAG = 'image_variation';
    private const IMAGE_VARIATION_SITEACCESS_TAG = 'image_variation_siteaccess';
    private const IMAGE_VARIATION_CONTENT_TAG = 'image_variation_content';
    private const IMAGE_VARIATION_FIELD_TAG = 'image_variation_field';
    private const IMAGE_VARIATION_NAME_TAG = 'image_variation_name';
    private const CONTENT_TAG = 'content';
    private const CONTENT_VERSION_TAG = 'content_version';

    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $aliasGenerator;

    /** @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface */
    private $cache;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    /** @var \Symfony\Component\Routing\RequestContext */
    private $requestContext;

    /** @var \eZ\Publish\Core\Persistence\Cache\Tags\TagGeneratorInterface */
    private $tagGenerator;

    /**
     * @param \eZ\Publish\SPI\Variation\VariationHandler $aliasGenerator
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cache
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     */
    public function __construct(
        VariationHandler $aliasGenerator,
        TagAwareAdapterInterface $cache,
        RequestContext $requestContext,
        TagGeneratorInterface $tagGenerator
    ) {
        $this->aliasGenerator = $aliasGenerator;
        $this->cache = $cache;
        $this->requestContext = $requestContext;
        $this->tagGenerator = $tagGenerator;
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
            $this->tagGenerator->generate(self::PREFIXED_IMAGE_VARIATION_TAG) . '-%s-%s-%s-%d-%d-%d-%s-%s',
            $this->siteAccess->name ?? 'default',
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
            $this->tagGenerator->generate(self::IMAGE_VARIATION_TAG),
            $this->tagGenerator->generate(self::IMAGE_VARIATION_NAME_TAG, [$variationName]),
            $this->tagGenerator->generate(self::IMAGE_VARIATION_SITEACCESS_TAG, [$this->siteAccess->name ?? 'default']),
            $this->tagGenerator->generate(self::IMAGE_VARIATION_CONTENT_TAG, [$contentId]),
            $this->tagGenerator->generate(self::IMAGE_VARIATION_FIELD_TAG, [$field->id]),
            $this->tagGenerator->generate(self::CONTENT_TAG, [$contentId]),
            $this->tagGenerator->generate(self::CONTENT_TAG, [$contentId]),
            $this->tagGenerator->generate(self::CONTENT_VERSION_TAG, [$contentId, $versionInfo->versionNo]),
        ];
    }
}
