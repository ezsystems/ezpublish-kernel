<?php

/**
 * ContentTypeService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;

/**
 * SiteAccess aware implementation of ContentTypeService injecting languages where needed.
 */
class ContentTypeService implements ContentTypeServiceInterface
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $service;

    /** @var \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $service
     * @param \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver $languageResolver
     */
    public function __construct(
        ContentTypeServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
    }

    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        return $this->service->createContentTypeGroup($contentTypeGroupCreateStruct);
    }

    public function loadContentTypeGroup($contentTypeGroupId, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentTypeGroup($contentTypeGroupId, $prioritizedLanguages);
    }

    public function loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, $prioritizedLanguages);
    }

    public function loadContentTypeGroups(array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentTypeGroups($prioritizedLanguages);
    }

    public function updateContentTypeGroup(ContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct)
    {
        return $this->service->updateContentTypeGroup($contentTypeGroup, $contentTypeGroupUpdateStruct);
    }

    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup)
    {
        return $this->service->deleteContentTypeGroup($contentTypeGroup);
    }

    public function createContentType(ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups)
    {
        return $this->service->createContentType($contentTypeCreateStruct, $contentTypeGroups);
    }

    public function loadContentType($contentTypeId, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentType($contentTypeId, $prioritizedLanguages);
    }

    public function loadContentTypeByIdentifier($identifier, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentTypeByIdentifier($identifier, $prioritizedLanguages);
    }

    public function loadContentTypeByRemoteId($remoteId, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentTypeByRemoteId($remoteId, $prioritizedLanguages);
    }

    public function loadContentTypeDraft($contentTypeId)
    {
        return $this->service->loadContentTypeDraft($contentTypeId);
    }

    public function loadContentTypeList(array $contentTypeIds, array $prioritizedLanguages = []): iterable
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentTypeList($contentTypeIds, $prioritizedLanguages);
    }

    public function loadContentTypes(ContentTypeGroup $contentTypeGroup, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadContentTypes($contentTypeGroup, $prioritizedLanguages);
    }

    public function createContentTypeDraft(ContentType $contentType)
    {
        return $this->service->createContentTypeDraft($contentType);
    }

    public function updateContentTypeDraft(ContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct)
    {
        return $this->service->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
    }

    public function deleteContentType(ContentType $contentType)
    {
        return $this->service->deleteContentType($contentType);
    }

    public function copyContentType(ContentType $contentType, User $user = null)
    {
        return $this->service->copyContentType($contentType, $user);
    }

    public function assignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup)
    {
        return $this->service->assignContentTypeGroup($contentType, $contentTypeGroup);
    }

    public function unassignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup)
    {
        return $this->service->unassignContentTypeGroup($contentType, $contentTypeGroup);
    }

    public function addFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct)
    {
        return $this->service->addFieldDefinition($contentTypeDraft, $fieldDefinitionCreateStruct);
    }

    public function removeFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition)
    {
        return $this->service->removeFieldDefinition($contentTypeDraft, $fieldDefinition);
    }

    public function updateFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct)
    {
        return $this->service->updateFieldDefinition($contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct);
    }

    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft)
    {
        return $this->service->publishContentTypeDraft($contentTypeDraft);
    }

    public function newContentTypeGroupCreateStruct($identifier)
    {
        return $this->service->newContentTypeGroupCreateStruct($identifier);
    }

    public function newContentTypeCreateStruct($identifier)
    {
        return $this->service->newContentTypeCreateStruct($identifier);
    }

    public function newContentTypeUpdateStruct()
    {
        return $this->service->newContentTypeUpdateStruct();
    }

    public function newContentTypeGroupUpdateStruct()
    {
        return $this->service->newContentTypeGroupUpdateStruct();
    }

    public function newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier)
    {
        return $this->service->newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier);
    }

    public function newFieldDefinitionUpdateStruct()
    {
        return $this->service->newFieldDefinitionUpdateStruct();
    }

    public function isContentTypeUsed(ContentType $contentType)
    {
        return $this->service->isContentTypeUsed($contentType);
    }
}
