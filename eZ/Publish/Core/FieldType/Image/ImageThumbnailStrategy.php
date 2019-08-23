<?php

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy;
use eZ\Publish\SPI\Variation\VariationHandler;

class ImageThumbnailStrategy implements ThumbnailStrategy
{
    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $variationHandler;

    /** @var string */
    private $variationName;

    public function __construct(
        VariationHandler $variationHandler,
        string $variationName
    ) {
        $this->variationHandler = $variationHandler;
        $this->variationName = $variationName;
    }

    public function getThumbnail(Field $field): ?Thumbnail
    {
        /** @var \eZ\Publish\SPI\Variation\Values\ImageVariation $variation */
        $variation = $this->variationHandler->getVariation($field, new VersionInfo(), $this->variationName);

        return new Thumbnail([
            'resource' => $variation->uri,
            'width' => $variation->width,
            'height' => $variation->height,
            'mimeType' => $variation->mimeType,
        ]);
    }
}
