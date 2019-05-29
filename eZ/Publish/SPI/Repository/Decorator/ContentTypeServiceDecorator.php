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
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $innerService;

    public function __construct(ContentTypeService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        $this->innerService->createContentTypeGroup($contentTypeGroupCreateStruct);
    }

    public function loadContentTypeGroup(
        $contentTypeGroupId,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadContentTypeGroup($contentTypeGroupId, $prioritizedLanguages);
    }

    public function loadContentTypeGroupByIdentifier(
        $contentTypeGroupIdentifier,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, $prioritizedLanguages);
    }

    public function loadContentTypeGroups(array $prioritizedLanguages = [])
    {
        $this->innerService->loadContentTypeGroups($prioritizedLanguages);
    }

    public function updateContentTypeGroup(
        ContentTypeGroup $contentTypeGroup,
        ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
    ) {
        $this->innerService->updateContentTypeGroup($contentTypeGroup, $contentTypeGroupUpdateStruct);
    }

    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup)
    {
        $this->innerService->deleteContentTypeGroup($contentTypeGroup);
    }

    public function createContentType(
        ContentTypeCreateStruct $contentTypeCreateStruct,
        array $contentTypeGroups
    ) {
        $this->innerService->createContentType($contentTypeCreateStruct, $contentTypeGroups);
    }

    public function loadContentType(
        $contentTypeId,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadContentType($contentTypeId, $prioritizedLanguages);
    }

    public function loadContentTypeByIdentifier(
        $identifier,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadContentTypeByIdentifier($identifier, $prioritizedLanguages);
    }

    public function loadContentTypeByRemoteId(
        $remoteId,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadContentTypeByRemoteId($remoteId, $prioritizedLanguages);
    }

    public function loadContentTypeDraft($contentTypeId)
    {
        $this->innerService->loadContentTypeDraft($contentTypeId);
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
        $this->innerService->loadContentTypes($contentTypeGroup, $prioritizedLanguages);
    }

    public function createContentTypeDraft(ContentType $contentType)
    {
        $this->innerService->createContentTypeDraft($contentType);
    }

    public function updateContentTypeDraft(
        ContentTypeDraft $contentTypeDraft,
        ContentTypeUpdateStruct $contentTypeUpdateStruct
    ) {
        $this->innerService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
    }

    public function deleteContentType(ContentType $contentType)
    {
        $this->innerService->deleteContentType($contentType);
    }

    public function copyContentType(
        ContentType $contentType,
        User $creator = null
    ) {
        $this->innerService->copyContentType($contentType, $creator);
    }

    public function assignContentTypeGroup(
        ContentType $contentType,
        ContentTypeGroup $contentTypeGroup
    ) {
        $this->innerService->assignContentTypeGroup($contentType, $contentTypeGroup);
    }

    public function unassignContentTypeGroup(
        ContentType $contentType,
        ContentTypeGroup $contentTypeGroup
    ) {
        $this->innerService->unassignContentTypeGroup($contentType, $contentTypeGroup);
    }

    public function addFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
    ) {
        $this->innerService->addFieldDefinition($contentTypeDraft, $fieldDefinitionCreateStruct);
    }

    public function removeFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition
    ) {
        $this->innerService->removeFieldDefinition($contentTypeDraft, $fieldDefinition);
    }

    public function updateFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition,
        FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
    ) {
        $this->innerService->updateFieldDefinition($contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct);
    }

    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft)
    {
        $this->innerService->publishContentTypeDraft($contentTypeDraft);
    }

    public function newContentTypeGroupCreateStruct($identifier)
    {
        $this->innerService->newContentTypeGroupCreateStruct($identifier);
    }

    public function newContentTypeCreateStruct($identifier)
    {
        $this->innerService->newContentTypeCreateStruct($identifier);
    }

    public function newContentTypeUpdateStruct()
    {
        $this->innerService->newContentTypeUpdateStruct();
    }

    public function newContentTypeGroupUpdateStruct()
    {
        $this->innerService->newContentTypeGroupUpdateStruct();
    }

    public function newFieldDefinitionCreateStruct(
        $identifier,
        $fieldTypeIdentifier
    ) {
        $this->innerService->newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier);
    }

    public function newFieldDefinitionUpdateStruct()
    {
        $this->innerService->newFieldDefinitionUpdateStruct();
    }

    public function isContentTypeUsed(ContentType $contentType)
    {
        $this->innerService->isContentTypeUsed($contentType);
    }

    public function removeContentTypeTranslation(
        ContentTypeDraft $contentTypeDraft,
        string $languageCode
    ): ContentTypeDraft {
        return $this->innerService->removeContentTypeTranslation($contentTypeDraft, $languageCode);
    }
}
