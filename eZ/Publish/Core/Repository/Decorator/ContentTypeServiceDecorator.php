<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

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
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\ContentTypeService $service
     */
    public function __construct(ContentTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        return $this->service->createContentTypeGroup($contentTypeGroupCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroup($contentTypeGroupId, array $prioritizedLanguages = [])
    {
        return $this->service->loadContentTypeGroup($contentTypeGroupId, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, array $prioritizedLanguages = [])
    {
        return $this->service->loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroups(array $prioritizedLanguages = [])
    {
        return $this->service->loadContentTypeGroups($prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContentTypeGroup(ContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct)
    {
        return $this->service->updateContentTypeGroup($contentTypeGroup, $contentTypeGroupUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup)
    {
        return $this->service->deleteContentTypeGroup($contentTypeGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function createContentType(ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups)
    {
        return $this->service->createContentType($contentTypeCreateStruct, $contentTypeGroups);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentType($contentTypeId, array $prioritizedLanguages = [])
    {
        return $this->service->loadContentType($contentTypeId, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeByIdentifier($identifier, array $prioritizedLanguages = [])
    {
        return $this->service->loadContentTypeByIdentifier($identifier, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeByRemoteId($remoteId, array $prioritizedLanguages = [])
    {
        return $this->service->loadContentTypeByRemoteId($remoteId, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeDraft($contentTypeId)
    {
        return $this->service->loadContentTypeDraft($contentTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeList(array $contentTypeIds, array $prioritizedLanguages = []): iterable
    {
        return $this->service->loadContentTypeList($contentTypeIds, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypes(ContentTypeGroup $contentTypeGroup, array $prioritizedLanguages = [])
    {
        return $this->service->loadContentTypes($contentTypeGroup, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function createContentTypeDraft(ContentType $contentType)
    {
        return $this->service->createContentTypeDraft($contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContentTypeDraft(ContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct)
    {
        return $this->service->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContentType(ContentType $contentType)
    {
        return $this->service->deleteContentType($contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function copyContentType(ContentType $contentType, User $creator = null)
    {
        return $this->service->copyContentType($contentType, $creator);
    }

    /**
     * {@inheritdoc}
     */
    public function assignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup)
    {
        return $this->service->assignContentTypeGroup($contentType, $contentTypeGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function unassignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup)
    {
        return $this->service->unassignContentTypeGroup($contentType, $contentTypeGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct)
    {
        return $this->service->addFieldDefinition($contentTypeDraft, $fieldDefinitionCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition)
    {
        return $this->service->removeFieldDefinition($contentTypeDraft, $fieldDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function updateFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition,
        FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
    ) {
        return $this->service->updateFieldDefinition($contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft)
    {
        return $this->service->publishContentTypeDraft($contentTypeDraft);
    }

    /**
     * {@inheritdoc}
     */
    public function newContentTypeGroupCreateStruct($identifier)
    {
        return $this->service->newContentTypeGroupCreateStruct($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function newContentTypeCreateStruct($identifier)
    {
        return $this->service->newContentTypeCreateStruct($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function newContentTypeUpdateStruct()
    {
        return $this->service->newContentTypeUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function newContentTypeGroupUpdateStruct()
    {
        return $this->service->newContentTypeGroupUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier)
    {
        return $this->service->newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function newFieldDefinitionUpdateStruct()
    {
        return $this->service->newFieldDefinitionUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function isContentTypeUsed(ContentType $contentType)
    {
        return $this->service->isContentTypeUsed($contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function removeContentTypeTranslation(ContentTypeDraft $contentTypeDraft, string $languageCode): ContentTypeDraft
    {
        return $this->service->removeContentTypeTranslation($contentTypeDraft, $languageCode);
    }
}
