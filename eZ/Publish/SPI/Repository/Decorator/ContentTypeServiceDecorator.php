<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;

abstract class ContentTypeServiceDecorator implements ContentTypeService
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $innerService;

    public function __construct(ContentTypeService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        return $this->innerService->createContentTypeGroup($contentTypeGroupCreateStruct);
    }

    public function loadContentTypeGroup(
        $contentTypeGroupId,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadContentTypeGroup($contentTypeGroupId, $prioritizedLanguages);
    }

    public function loadContentTypeGroupByIdentifier(
        $contentTypeGroupIdentifier,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, $prioritizedLanguages);
    }

    public function loadContentTypeGroups(array $prioritizedLanguages = [])
    {
        return $this->innerService->loadContentTypeGroups($prioritizedLanguages);
    }

    public function updateContentTypeGroup(
        ContentTypeGroup $contentTypeGroup,
        ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
    ) {
        return $this->innerService->updateContentTypeGroup($contentTypeGroup, $contentTypeGroupUpdateStruct);
    }

    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup)
    {
        return $this->innerService->deleteContentTypeGroup($contentTypeGroup);
    }

    public function createContentType(
        ContentTypeCreateStruct $contentTypeCreateStruct,
        array $contentTypeGroups
    ) {
        return $this->innerService->createContentType($contentTypeCreateStruct, $contentTypeGroups);
    }

    public function loadContentType(
        $contentTypeId,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadContentType($contentTypeId, $prioritizedLanguages);
    }

    public function loadContentTypeByIdentifier(
        $identifier,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadContentTypeByIdentifier($identifier, $prioritizedLanguages);
    }

    public function loadContentTypeByRemoteId(
        $remoteId,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadContentTypeByRemoteId($remoteId, $prioritizedLanguages);
    }

    public function loadContentTypeDraft($contentTypeId)
    {
        return $this->innerService->loadContentTypeDraft($contentTypeId);
    }

    public function loadContentTypeList(
        array $contentTypeIds,
        array $prioritizedLanguages = []
    ): iterable {
        return $this->innerService->loadContentTypeList($contentTypeIds, $prioritizedLanguages);
    }

    public function loadContentTypes(
        ContentTypeGroup $contentTypeGroup,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadContentTypes($contentTypeGroup, $prioritizedLanguages);
    }

    public function createContentTypeDraft(ContentType $contentType)
    {
        return $this->innerService->createContentTypeDraft($contentType);
    }

    public function updateContentTypeDraft(
        ContentTypeDraft $contentTypeDraft,
        ContentTypeUpdateStruct $contentTypeUpdateStruct
    ) {
        return $this->innerService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
    }

    public function deleteContentType(ContentType $contentType)
    {
        return $this->innerService->deleteContentType($contentType);
    }

    public function copyContentType(
        ContentType $contentType,
        User $creator = null
    ) {
        return $this->innerService->copyContentType($contentType, $creator);
    }

    public function assignContentTypeGroup(
        ContentType $contentType,
        ContentTypeGroup $contentTypeGroup
    ) {
        return $this->innerService->assignContentTypeGroup($contentType, $contentTypeGroup);
    }

    public function unassignContentTypeGroup(
        ContentType $contentType,
        ContentTypeGroup $contentTypeGroup
    ) {
        return $this->innerService->unassignContentTypeGroup($contentType, $contentTypeGroup);
    }

    public function addFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
    ) {
        return $this->innerService->addFieldDefinition($contentTypeDraft, $fieldDefinitionCreateStruct);
    }

    public function removeFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition
    ) {
        return $this->innerService->removeFieldDefinition($contentTypeDraft, $fieldDefinition);
    }

    public function updateFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition,
        FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
    ) {
        return $this->innerService->updateFieldDefinition($contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct);
    }

    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft)
    {
        return $this->innerService->publishContentTypeDraft($contentTypeDraft);
    }

    public function newContentTypeGroupCreateStruct($identifier)
    {
        return $this->innerService->newContentTypeGroupCreateStruct($identifier);
    }

    public function newContentTypeCreateStruct($identifier)
    {
        return $this->innerService->newContentTypeCreateStruct($identifier);
    }

    public function newContentTypeUpdateStruct()
    {
        return $this->innerService->newContentTypeUpdateStruct();
    }

    public function newContentTypeGroupUpdateStruct()
    {
        return $this->innerService->newContentTypeGroupUpdateStruct();
    }

    public function newFieldDefinitionCreateStruct(
        $identifier,
        $fieldTypeIdentifier
    ) {
        return $this->innerService->newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier);
    }

    public function newFieldDefinitionUpdateStruct()
    {
        return $this->innerService->newFieldDefinitionUpdateStruct();
    }

    public function isContentTypeUsed(ContentType $contentType)
    {
        return $this->innerService->isContentTypeUsed($contentType);
    }

    public function removeContentTypeTranslation(
        ContentTypeDraft $contentTypeDraft,
        string $languageCode
    ): ContentTypeDraft {
        return $this->innerService->removeContentTypeTranslation($contentTypeDraft, $languageCode);
    }
}
