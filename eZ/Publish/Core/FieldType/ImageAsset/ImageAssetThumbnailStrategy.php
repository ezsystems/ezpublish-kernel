<?php

namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy as ContentThumbnailStrategy;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy;

class ImageAssetThumbnailStrategy implements ThumbnailStrategy
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy */
    private $thumbnailStrategy;

    public function __construct(ContentThumbnailStrategy $thumbnailStrategy, ContentService $contentService)
    {
        $this->contentService = $contentService;
        $this->thumbnailStrategy = $thumbnailStrategy;
    }

    public function getThumbnail(Field $field): ?Thumbnail
    {
        $content = $this->contentService->loadContent($field->value->destinationContentId);

        return $this->thumbnailStrategy->getThumbnail(
            $content->getContentType(),
            $content->getFields()
        );
    }
}
