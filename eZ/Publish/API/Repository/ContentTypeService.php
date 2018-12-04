<?php

/**
 * File containing the eZ\Publish\API\Repository\ContentTypeService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;

/**
 * @example Examples/contenttype.php
 */
interface ContentTypeService
{
    /**
     * Create a Content Type Group object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a group with the same identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct);

    /**
     * Get a Content Type Group object by id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param mixed $contentTypeGroupId
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup($contentTypeGroupId, array $prioritizedLanguages = []);

    /**
     * Get a Content Type Group object by identifier.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param string $contentTypeGroupIdentifier
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, array $prioritizedLanguages = []);

    /**
     * Get all Content Type Groups.
     *
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function loadContentTypeGroups(array $prioritizedLanguages = []);

    /**
     * Update a Content Type Group object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier (if set) already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function updateContentTypeGroup(ContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct);

    /**
     * Delete a Content Type Group.
     *
     * This method only deletes an content type group which has content types without any content instances
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If  a to be deleted content type has instances
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $ContentTypeGroup
     */
    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup);

    /**
     * Create a Content Type object.
     *
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException In case when
     *         - array of content type groups does not contain at least one content type group
     *         - identifier or remoteId in the content type create struct already exists
     *         - there is a duplicate field identifier in the content type create struct
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     *         if a field definition in the $contentTypeCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups Required array of
     *        {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType(ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups);

    /**
     * Get a Content Type object by id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If a content type with the given id and status DEFINED can not be found
     *
     * @param mixed $contentTypeId
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentType($contentTypeId, array $prioritizedLanguages = []);

    /**
     * Get a Content Type object by identifier.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given identifier and status DEFINED can not be found
     *
     * @param string $identifier
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier($identifier, array $prioritizedLanguages = []);

    /**
     * Get a Content Type object by id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given remote id and status DEFINED can not be found
     *
     * @param string $remoteId
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId($remoteId, array $prioritizedLanguages = []);

    /**
     * Get a Content Type object draft by id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param mixed $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function loadContentTypeDraft($contentTypeId);

    /**
     * Bulk-load Content Type objects by ids.
     *
     * Note: it does not throw exceptions on load, just ignores erroneous items.
     *
     * @since 7.3
     *
     * @param mixed[] $contentTypeIds
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[]|iterable
     */
    public function loadContentTypeList(array $contentTypeIds, array $prioritizedLanguages = []): iterable;

    /**
     * Get Content Type objects which belong to the given content type group.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[] an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes(ContentTypeGroup $contentTypeGroup, array $prioritizedLanguages = []);

    /**
     * Creates a draft from an existing content type.
     *
     * This is a complete copy of the content
     * type which has the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there is already a draft assigned to another user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentTypeDraft(ContentType $contentType);

    /**
     * Update a Content Type object.
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier or remoteId already exists.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function updateContentTypeDraft(ContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct);

    /**
     * Delete a Content Type object.
     *
     * Deletes a content type if it has no instances. If content type in state STATUS_DRAFT is
     * given, only the draft content type will be deleted. Otherwise, if content type in state
     * STATUS_DEFINED is given, all content type data will be deleted.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there exist content objects of this type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     */
    public function deleteContentType(ContentType $contentType);

    /**
     * Copy Type incl fields and groupIds to a new Type object.
     *
     * New Type will have $creator as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current-user is not allowed to copy a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\User\User $creator If null the current-user is used instead
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function copyContentType(ContentType $contentType, User $creator = null);

    /**
     * Assigns a content type to a content type group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type is already assigned the given group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function assignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup);

    /**
     * Unassign a content type from a group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to link a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type is not assigned this the given group.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If $contentTypeGroup is the last group assigned to the content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function unassignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup);

    /**
     * Adds a new field definition to an existing content type.
     *
     * The content type must be in state DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the identifier in already exists in the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     *         if a field definition in the $contentTypeCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If field definition of the same non-repeatable type is being
     *                                                                 added to the ContentType that already contains one
     *                                                                 or field definition that can't be added to a ContentType that
     *                                                                 has Content instances is being added to such ContentType
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function addFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct);

    /**
     * Remove a field definition from an existing Type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given field definition does not belong to the given type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function removeFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition);

    /**
     * Update a field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type of
     *                                                                        If the given identifier is used in an existing field of the given content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct);

    /**
     * Publish the content type and update content objects.
     *
     * This method updates content objects, depending on the changed field definitions.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If the content type has no draft
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type has no field definitions
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     */
    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft);

    /**
     * Instantiates a new content type group create class.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct($identifier);

    /**
     * Instantiates a new content type create class.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct($identifier);

    /**
     * Instantiates a new content type update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct();

    /**
     * Instantiates a new content type update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct();

    /**
     * Instantiates a field definition create struct.
     *
     * @param string $fieldTypeIdentifier the required field type identifier
     * @param string $identifier the required identifier for the field definition
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier);

    /**
     * Instantiates a field definition update class.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct();

    /**
     * Returns true if the given content type $contentType has content instances.
     *
     * @since 6.0.1
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return bool
     */
    public function isContentTypeUsed(ContentType $contentType);
}
