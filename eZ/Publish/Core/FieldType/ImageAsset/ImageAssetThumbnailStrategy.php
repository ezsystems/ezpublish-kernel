<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\FieldTypeBasedThumbnailStrategy;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy as ContentThumbnailStrategy;

class ImageAssetThumbnailStrategy implements FieldTypeBasedThumbnailStrategy
{
    /** @var string */
    private $fieldTypeIdentifier;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy */
    private $thumbnailStrategy;

    public function __construct(
        string $fieldTypeIdentifier,
        ContentThumbnailStrategy $thumbnailStrategy,
        ContentService $contentService
    ) {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->contentService = $contentService;
        $this->thumbnailStrategy = $thumbnailStrategy;
    }

    public function getFieldTypeIdentifier(): string
    {
        return $this->fieldTypeIdentifier;
    }

    public function getThumbnail(Field $field): ?Thumbnail
    {
        try {
            $content = $this->contentService->loadContent((int) $field->value->destinationContentId);
        } catch (NotFoundException $e) {
            return null;
        }

        return $this->thumbnailStrategy->getThumbnail(
            $content->getContentType(),
            $content->getFields()
        );
    }
}
