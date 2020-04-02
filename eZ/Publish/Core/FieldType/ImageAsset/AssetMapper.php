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
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class AssetMapper
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var int */
    private $contentTypeId = null;

    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        ConfigResolverInterface $configResolver)
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->configResolver = $configResolver;
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
        $mappings = $this->getMappings();

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $mappings['content_type_identifier']
        );

        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $languageCode);
        $contentCreateStruct->setField($mappings['name_field_identifier'], $name);
        $contentCreateStruct->setField($mappings['content_field_identifier'], $image);

        $contentDraft = $this->contentService->createContent($contentCreateStruct, [
            $this->locationService->newLocationCreateStruct($mappings['parent_location_id']),
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
            throw new InvalidArgumentException('contentId', "Content {$content->id} is not an image asset.");
        }

        return $content->getField($this->getContentFieldIdentifier());
    }

    /**
     * Returns definition of the field which is used to store value of the Image Asset.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getAssetFieldDefinition(): FieldDefinition
    {
        $mappings = $this->getMappings();

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $mappings['content_type_identifier']
        );

        return $contentType->getFieldDefinition(
            $mappings['content_field_identifier']
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
            throw new InvalidArgumentException('contentId', "Content {$content->id} is not an image asset.");
        }

        return $content->getFieldValue($this->getContentFieldIdentifier());
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
                $this->getContentTypeIdentifier()
            );

            $this->contentTypeId = $contentType->id;
        }

        return $content->contentInfo->contentTypeId === $this->contentTypeId;
    }

    /**
     * Return identifier of the Content Type used as Assets.
     */
    public function getContentTypeIdentifier(): string
    {
        return $this->getMappings()['content_type_identifier'];
    }

    /**
     * Return identifier of the field used to store Image Asset value.
     *
     * @return string
     */
    public function getContentFieldIdentifier(): string
    {
        return $this->getMappings()['content_field_identifier'];
    }

    /**
     * Return ID of the base location for the Image Assets.
     *
     * @return int
     */
    public function getParentLocationId(): int
    {
        return $this->getMappings()['parent_location_id'];
    }

    protected function getMappings(): array
    {
        return $this->configResolver->getParameter('fieldtypes.ezimageasset.mappings');
    }
}
