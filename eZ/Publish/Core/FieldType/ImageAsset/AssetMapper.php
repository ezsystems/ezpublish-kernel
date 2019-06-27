<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;

class AssetMapper
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var array */
    private $mappings = [];

    /** @var int */
    private $contentTypeId = null;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param array $mappings
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        array $mappings)
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->mappings = $mappings;
    }

    /**
     * Creates an Image Asset.
     *
     * @param string $name
     * @param \eZ\Publish\Core\FieldType\Image\Value $image
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function createAsset(string $name, ImageValue $image, string $languageCode): Content
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $this->mappings['content_type_identifier']
        );

        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $languageCode);
        $contentCreateStruct->setField($this->mappings['name_field_identifier'], $name);
        $contentCreateStruct->setField($this->mappings['content_field_identifier'], $image);

        $contentDraft = $this->contentService->createContent($contentCreateStruct, [
            $this->locationService->newLocationCreateStruct($this->mappings['parent_location_id']),
        ]);

        return $this->contentService->publishVersion($contentDraft->versionInfo);
    }

    /**
     * Returns field which is used to store the Image Asset value from specified content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getAssetField(Content $content): Field
    {
        if (!$this->isAsset($content)) {
            throw new InvalidArgumentException('contentId', "Content {$content->id} is not a image asset!");
        }

        return $content->getField($this->mappings['content_field_identifier']);
    }

    /**
     * Returns definition of the field which is used to store value of the Image Asset.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getAssetFieldDefinition(): FieldDefinition
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $this->mappings['content_type_identifier']
        );

        return $contentType->getFieldDefinition(
            $this->mappings['content_field_identifier']
        );
    }

    /**
     * Returns field value of the Image Asset from specified content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getAssetValue(Content $content): ImageValue
    {
        if (!$this->isAsset($content)) {
            throw new InvalidArgumentException('contentId', "Content {$content->id} is not a image asset!");
        }

        return $content->getFieldValue($this->mappings['content_field_identifier']);
    }

    /**
     * Returns TRUE if content is an Image Asset.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return bool
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function isAsset(Content $content): bool
    {
        if ($this->contentTypeId === null) {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
                $this->mappings['content_type_identifier']
            );

            $this->contentTypeId = $contentType->id;
        }

        return $content->contentInfo->contentTypeId === $this->contentTypeId;
    }

    /**
     * Return identifier of the field used to store Image Asset value.
     *
     * @return string
     */
    public function getContentFieldIdentifier(): string
    {
        return $this->mappings['content_field_identifier'];
    }

    /**
     * Return ID of the base location for the Image Assets.
     *
     * @return int
     */
    public function getParentLocationId(): int
    {
        return $this->mappings['parent_location_id'];
    }
}
