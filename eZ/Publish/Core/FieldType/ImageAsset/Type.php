<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIContentHandler;
use eZ\Publish\Core\FieldType\Value as BaseValue;

class Type extends FieldType
{
    const FIELD_TYPE_IDENTIFIER = 'ezimageasset';

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper
     */
    private $assetMapper;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    private $handler;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper $mapper
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $handler
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        AssetMapper $mapper,
        SPIContentHandler $handler
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->assetMapper = $mapper;
        $this->handler = $handler;
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\ImageAsset\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue): array
    {
        $errors = [];

        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }

        $content = $this->contentService->loadContent($fieldValue->destinationContentId);

        if (!$this->assetMapper->isAsset($content)) {
            $currentContentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);

            $errors[] = new ValidationError(
                'Content %type% is not a valid asset target',
                null,
                [
                    '%type%' => $currentContentType->identifier,
                ],
                'destinationContentId'
            );
        }

        return $errors;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier(): string
    {
        return self::FIELD_TYPE_IDENTIFIER;
    }

    /**
     * @param \eZ\Publish\Core\FieldType\ImageAsset\Value|\eZ\Publish\SPI\FieldType\Value $value
     */
    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        if (empty($value->destinationContentId)) {
            return '';
        }

        try {
            $contentInfo = $this->handler->loadContentInfo($value->destinationContentId);
            $versionInfo = $this->handler->loadVersionInfo($value->destinationContentId, $contentInfo->currentVersionNo);
        } catch (NotFoundException $e) {
            return '';
        }

        return $versionInfo->names[$languageCode] ?? $versionInfo->names[$contentInfo->mainLanguageCode];
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\ImageAsset\Value
     */
    public function getEmptyValue(): Value
    {
        return new Value();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value): bool
    {
        return null === $value->destinationContentId;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param int|string|\eZ\Publish\API\Repository\Values\Content\ContentInfo|\eZ\Publish\Core\FieldType\Relation\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\ImageAsset\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if ($inputValue instanceof ContentInfo) {
            $inputValue = new Value($inputValue->id);
        } elseif (is_int($inputValue) || is_string($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\ImageAsset\Value $value
     */
    protected function checkValueStructure(BaseValue $value): void
    {
        if (!is_int($value->destinationContentId) && !is_string($value->destinationContentId)) {
            throw new InvalidArgumentType(
                '$value->destinationContentId',
                'string|int',
                $value->destinationContentId
            );
        }

        if ($value->alternativeText !== null && !is_string($value->alternativeText)) {
            throw new InvalidArgumentType(
                '$value->alternativeText',
                'string|null',
                $value->alternativeText
            );
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     * For this FieldType, the related object's name is returned.
     *
     * @param \eZ\Publish\Core\FieldType\Relation\Value $value
     *
     * @return bool
     */
    protected function getSortInfo(BaseValue $value): bool
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\ImageAsset\Value $value
     */
    public function fromHash($hash): Value
    {
        if ($hash) {
            return new Value($hash['destinationContentId'], $hash['alternativeText']);
        }

        return new Value();
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\ImageAsset\Value $value
     *
     * @return array
     */
    public function toHash(SPIValue $value): array
    {
        return [
            'destinationContentId' => $value->destinationContentId,
            'alternativeText' => $value->alternativeText,
        ];
    }

    /**
     * Returns relation data extracted from value.
     *
     * Not intended for \eZ\Publish\API\Repository\Values\Content\Relation::COMMON type relations,
     * there is an API for handling those.
     *
     * @param \eZ\Publish\Core\FieldType\ImageAsset\Value $fieldValue
     *
     * @return array Hash with relation type as key and array of destination content ids as value.
     *
     * Example:
     * <code>
     *  array(
     *      \eZ\Publish\API\Repository\Values\Content\Relation::LINK => array(
     *          "contentIds" => array( 12, 13, 14 ),
     *          "locationIds" => array( 24 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::EMBED => array(
     *          "contentIds" => array( 12 ),
     *          "locationIds" => array( 24, 45 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::FIELD => array( 12 )
     *  )
     * </code>
     */
    public function getRelations(SPIValue $fieldValue): array
    {
        $relations = [];
        if ($fieldValue->destinationContentId !== null) {
            $relations[Relation::ASSET] = [$fieldValue->destinationContentId];
        }

        return $relations;
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return true;
    }
}
