<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Imagine\ImageAsset;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\FieldType\ImageAsset\AssetMapper;
use eZ\Publish\SPI\FieldType\Value;
use eZ\Publish\SPI\Variation\Values\Variation;
use eZ\Publish\SPI\Variation\VariationHandler;
use eZ\Publish\Core\FieldType\ImageAsset\Value as ImageAssetValue;

/**
 * Alias Generator Decorator allowing generate variations based on passed ImageAsset\Value.
 */
class AliasGenerator implements VariationHandler
{
    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $innerAliasGenerator;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper */
    private $assetMapper;

    /**
     * @param \eZ\Publish\SPI\Variation\VariationHandler $innerAliasGenerator
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper $assetMapper
     */
    public function __construct(
        VariationHandler $innerAliasGenerator,
        ContentService $contentService,
        AssetMapper $assetMapper)
    {
        $this->innerAliasGenerator = $innerAliasGenerator;
        $this->contentService = $contentService;
        $this->assetMapper = $assetMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = []): Variation
    {
        if ($this->supportsValue($field->value)) {
            $destinationContent = $this->contentService->loadContent(
                $field->value->destinationContentId
            );

            return $this->innerAliasGenerator->getVariation(
                $this->assetMapper->getAssetField($destinationContent),
                $destinationContent->versionInfo,
                $variationName,
                $parameters
            );
        }

        return $this->innerAliasGenerator->getVariation($field, $versionInfo, $variationName, $parameters);
    }

    /**
     * Returns TRUE if the value is supported by alias generator.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return bool
     */
    public function supportsValue(Value $value): bool
    {
        return $value instanceof ImageAssetValue;
    }
}
