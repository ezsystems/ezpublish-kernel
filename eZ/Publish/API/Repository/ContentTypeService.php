<?php
/**
 * @package eZ\Publish\API\Repository
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
 *
 * @package eZ\Publish\API\Repository
 */
interface ContentTypeService
{
    /**
     * Create a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If a group with the same identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function createContentTypeGroup( ContentTypeGroupCreateStruct  $contentTypeGroupCreateStruct );

    /**
     * Get a Content Type Group object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param int $contentTypeGroupId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup( $contentTypeGroupId );

    /**
     * Get a Content Type Group object by identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     * 
     * @param string $contentTypeGroupIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroupByIdentifier( $contentTypeGroupIdentifier );

    /**
     * Get all Content Type Groups
     *
     * @return aray an array of {@link ContentTypeGroup}
     */
    public function loadContentTypeGroups();

    /**
     * Update a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given identifier (if set) already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function updateContentTypeGroup( ContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct );

    /**
     * Delete a Content Type Group. 
     * 
     * If the paramter $deleteObjects is set to true
     * this method deletes also all content types in this group which
     * are not assigned to other groups including the content object instances.
     * 
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the parameter $deleteObjects is set to false and a to be deleted content type
     *                                       has instances
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     * @param boolean $deleteObjects indicates if content object should be deleted if exist
     */
    public function deleteContentTypeGroup( ContentTypeGroup $contentTypeGroup, $deleteObjects = false );

    /**
     * Create a Content Type object. 
     * 
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the identifier or remoteId in the content type create struct already exists
     *         or there is a dublicate field identifier
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param array $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType( ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups );

    /**
     * Get a Content Type object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If a content type with the given id and status DEFINED can not be found
     *
     * @param int $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentType( $contentTypeId );

    /**
     * Get a Content Type object by identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given identifier and status DEFINED can not be found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier( $identifier );

    /**
     * Get a Content Type object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given remote id and status DEFINED can not be found
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId( $remoteId );

    /**
     * Get a Content Type object draft by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param int $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function loadContentTypeDraft( $contentTypeId );

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     *
     * @return array an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes( ContentTypeGroup $contentTypeGroup );

    /**
     * Creates a draft from an existing content type. 
     * 
     * This is a complete copy of the content
     * type wiich has the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there is already a draft assigned to another user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentTypeDraft( ContentType $contentType );

    /**
     * Update a Content Type object
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given identifier or remoteId already exists or there is no draft assigned to the authenticated user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function updateContentTypeDraft( ContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct );

    /**
     * Delete a Content Type object. 
     * 
     * If $deleteObjects is set to true all object instances of this content type are deleted.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException $deleteObjects is set to false and there exist content objects of this type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param boolean $deleteObjects indicates if content object should be deleted if exist
     */
    public function deleteContentType( ContentType $contentType , $deleteObjects = false );

    /**
     * Copy Type incl fields and groupIds to a new Type object
     *
     * New Type will have $userId as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to copy a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\User\User $user if null the current user is used
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function copyContentType( ContentType $contentType, User $user = null );

    /**
     * assign a content type to a content type group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the content type is already assigned the given group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function assignContentTypeGroup( ContentType $contentType, ContentTypeGroup $contentTypeGroup );

    /**
     * Unassign a content type from a group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to link a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the content type is not assigned this the given group.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If $contentTypeGroup is the last group assigned to the content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function unassignContentTypeGroup( ContentType $contentType, ContentTypeGroup $contentTypeGroup );

    /**
     * Adds a new field definition to an existing content type. 
     * 
     * The content type must be in state DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the identifier in already exists in the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function addFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct );

    /**
     * Remove a field definition from an existing Type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given field definition does not belong to the given type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function removeFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition );

    /**
     * Update a field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException  If the given identifier is used in an existing field of the given content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct );

    /**
     * Publish the content type and update content objects.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If the content type has no draft
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish a content type
     *
     * This method updates content objects, depending on the changed field definitions.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     */
    public function publishContentTypeDraft( ContentTypeDraft $contentTypeDraft );

    /**
     * instanciates a new content type group create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct( $identifier );

    /**
     * instanciates a new content type create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct( $identifier );

    /**
     * instanciates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct();

    /**
     * instanciates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct();

    /**
     * instanciates a field definition create struct
     *
     * @param string $fieldTypeIdentifier the required  field type identifier
     * @param string $identifier the required identifier for the field definition
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct( $identifier, $fieldTypeIdentifier );

    /**
     * instanciates a field definition update class
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct();

}
