<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\ContentType\FieldDefinitionUpdate;

use ezp\PublicAPI\Values\ContentType\FieldDefinition;

use ezp\PublicAPI\Values\ContentType\FieldDefinitionCreate;

use ezp\PublicAPI\Values\ContentType\ContentType;

use ezp\PublicAPI\Values\ContentType\ContentTypeDraft;

use ezp\PublicAPI\Values\ContentType\ContentTypeGroup;

use ezp\PublicAPI\Values\User\User;

use ezp\Values\ContentType\ContentTypeUpdate;

use ezp\PublicAPI\Values\Content\ContentType\ContentTypeCreate;

use ezp\PublicAPI\Values\ContentType\ContentTypeGroupUpdate;

use ezp\VPublicAPI\alues\ContentType\ContentTypeGroupCreate;

use ezp\PublicAPI\Interfaces\Exception\Forbidden;

use ezp\PublicAPI\Interfaces\Exception\NotFound;

use ezp\PublicAPI\Interfaces\Exception\Unauthorized;

/**
 * @example Examples/contenttype.php
 * @package ezp\PublicAPI\Interfaces
 */
interface ContentTypeService
{
    /**
     * Create a Content Type Group object
     *
     * @param ContentTypeGroupCreate $groupCreate
     * @return ContentTypeGroup
     * @throws Unauthorized if the user is not allowed to create a content type group
     * @throws Forbidden If a group with the same identifier already exists
     */
    public function createContentTypeGroup(/*ContentTypeGroupCreate*/  $contentTypeGroupCreate );

    /**
     * Get a Content Type Group object by id
     *
     * @param int $contentTypeGroupId
     * @return ContentTypeGroup
     * @throws NotFound If group can not be found
     */
    public function loadContentTypeGroup( $contentTypeGroupId );

    /**
     * Get a Content Type Group object by identifier
     *
     * @param string $contentTypeGroupIdentifier
     * @return ContentTypeGroup
     * @throws NotFound If group can not be found
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
     * @param ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param ContentTypeGroupUpdate $contentTypeGroupUpdate
     * @throws Unauthorized if the user is not allowed to create a content type group
     * @throws Forbidden If the given identifier (if set) already exists
     */
    public function updateContentTypeGroup(/*ContentTypeGroup*/ $contentTypeGroup, /*ContentTypeGroupUpdate*/ $contentTypeGroupUpdate );

    /**
     * Delete a Content Type Group. If the paramter $deleteObjects is set to true
     * this method deletes also all content types in this group which
     * are not assigned to other groups including the content object instances.
     *
     * @param ContentTypeGroup
     * @param boolean $deleteObjects indicates if content object should be deleted if exist
     * @throws Unauthorized if the user is not allowed to delete a content type group
     * @throws Forbidden If the parameter $deleteObjects is set to false and a to be deleted content type
     *                                       has instances
     */
    public function deleteContentTypeGroup( /*ContentTypeGroup*/ $contentTypeGroup, $deleteObjects = false );

    /**
     * Create a Content Type object. The content type is created in the state STATUS_DRAFT.
     *
     * @param ContentTypeCreate $contentTypeCreate
     * @param array $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     * @return ContentTypeDraft
     * @throws InvalidArgumentException If an identifier of a field definition is used in several fields
     * @throws Forbidden If the identifier or remoteId in the content type create struct already exists
     */
    public function createContentType( /*ContentTypeCreate*/ $contentTypeCreate, array $contentTypeGroups );

    /**
     * Get a Content Type object by id
     *
     * @param int $contentTypeId
     * @return ContentType
     * @throws NotFound If a content type with the given id and status DEFINED can not be found
     */
    public function loadContentType( $contentTypeId );

    /**
     * Get a Content Type object by identifier
     *
     * @param string $identifier
     * @return ContentType
     * @throws NotFound If content type with the given identifier and status DEFINED can not be found
     */
    public function loadContentTypeByIdentifier( $identifier );

    /**
     * Get a Content Type object by id
     *
     * @param string $remoteId
     * @return ContentType
     * @throws NotFound If content type with the given remote id and status DEFINED can not be found
     */
    public function loadContentTypeByRemoteId( $remoteId );


    /**
     * Get a Content Type object draft by id
     *
     * @param int $contentTypeId
     * @return ContentTypeDraft
     * @throws NotFound If the content type draft owned by the current user can not be found
     */
    public function loadContentTypeDraft( $contentTypeId );

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param ContentTypeGroup $contentTypeGroup
     * @return array an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes(/*ContentTypeGroup*/ $contentTypeGroup );
     
    /**
     * Creates a draft from an existing content type. This is a complete copy of the content
     * type wiich has the state STATUS_DRAFT.
     * @param ContentType $contentType
     * @return ContentTypeDraft
     * @throws Unauthorized if the user is not allowed to edit a content type
     * @throws Forbidden If there is already a draft assigned to another user
     */
    public function createContentTypeDraft(/*ContentType*/ $contentType);


    /**
     * Update a Content Type object
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * @param ContentTypeUpdate $contentTypeUpdate
     * @throws Unauthorized if the user is not allowed to update a content type
     * @throws Forbidden If the given identifier or remoteId already exists or there is no draft assigned to the authenticated user
     */
    public function updateContentTypeDraft(/*ContentTypeDraft*/ $contentTypeDraft, /*ContentTypeUpdate*/ $contentTypeUpdate );

    /**
     * Delete a Content Type object. If $deleteObjects is set to true all object instances of this content type are deleted.
     *
     * @param ContentType $contentType
     * @param boolean $deleteObjects indicates if content object should be deleted if exist
     * @throws Forbidden $deleteObjects is set to false and there exist content objects of this type
     * @throws Unauthorized if the user is not allowed to delete a content type
     */
    public function deleteContentType( /*ContentType*/ $contentType , $deleteObjects = false);

    /**
     * Copy Type incl fields and groupIds to a new Type object
     *
     * New Type will have $userId as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @param ContentType $contentType
     * @param User $user if null the current user is used
     * @return ContentType
     * @throws Unauthorized if the user is not allowed to copy a content type
     */
    public function copyContentType(/*ContentType*/ $contentType, /*User*/ $user = null );

    /**
     * Un-Link a content type from a group ( remove a group from a type )
     *
     * @param ContentType $contentType
     * @param ContentTypeGroup $contentTypeGroup
     * @throws InvalidArgumentValue If the content type is not assigned thi the given group.
     * @throws Unauthorized if the user is not allowed to unlink a content type
     * @throws Forbidden If $contentTypeGroup is the last group assigned to the content type
     */
    public function assignContentTypeGroup( /*ContentType*/ $contentType, /*ContentTypeGroup*/ $contentTypeGroup );

    /**
     * Link a content type to a group.
     *
     * @param ContentType $contentType
     * @param ContentTypeGroup $contentTypeGroup
     * @throws Unauthorized if the user is not allowed to link a content type
     * @throws Forbidden If the content type is already assigned thi the given group
     */
    public function unassignContentTypeGroup( /*ContentType*/ $contentType, /*ContentTypeGroup*/ $contentTypeGroup );

    /**
     * Adds a new field definition to an existing content type. The content type must be in state DRAFT.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * @param FieldDefinitionCreate $fieldDefinitionCreate
     * @throws Forbidden if the identifier in already exists in the content type
     * @throws Unauthorized if the user is not allowed to edit a content type
     */
    public function addFieldDefinition( /*ContentTypeDraft*/ $contentTypeDraft, /*FieldDefinitionCreate*/ $fieldDefinitionCreate  );

    /**
     * Remove a field definition from an existing Type.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * @param FieldDefinition $fieldDefinition
     * @throws InvalidArgumentException If the given field definition does not belong to the given type
     * @throws Unauthorized if the user is not allowed to edit a content type
     */
    public function removeFieldDefinition( /*ContentTypeDraft*/ $contentTypeDraft, /*FieldDefinition*/ $fieldDefinition  );

    /**
     * Update a field definition
     *
     * @param ContentTypeDraft $contentTypeDraft the content type draft
     * @param FieldDefinition $fieldDefinition the field definition which should be updated
     * @param FieldDefinitionUpdate $fieldDefinitionUpdate
     * @throws InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     * @throws Unauthorized if the user is not allowed to edit a content type
     * @throws Forbidden  If the given identifier is used in an existing field of the given content type
     */
    public function updateFieldDefinition( /*ContentTypeDraft*/ $contentTypeDraft, /*FieldDefinition*/ $fieldDefinition, /*FieldDefinitionUpdate*/ $fieldDefinitionUpdate  );

    /**
     * Publish the content type and update content objects.
     *
     * This method updates content objects, depending on the changed field definitions.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * @throws Forbidden If the content type has no draft
     * @throws Unauthorized if the user is not allowed to publish a content type
     */
    public function publishContentTypeDraft( /*ContentType*/ $contentTypeDraft  );

    /**
     *
     * instanciates a new content type group create class
     * @param string $identifier
     * @return ContentTypeGroupCreate
     */
    public function newContentTypeGroupCreate($identifier);

    /**
     *
     * instanciates a new content type create class
     * @param string $identifier
     * @return ContentTypeCreate
     */
    public function newContentTypeCreate($identifier);

    /**
     *
     * instanciates a new content type update class
     * @return ContentTypeUpdate
     */
    public function newContentTypeUpdate();

    /**
     * instanciates a new content type update class
     * @return ContentTypeGroupUpdate
     */
    public function newContentTypeGroupUpdate();

    /**
     * instanciates a field definition create class
     * @param string $fieldTypeIdentifier the required  field type identifier
     * @param string $identifier the required identifier for the field definition
     * @return FieldDefinitionCreate
     */
    public function newFieldDefinitionCreate($identifier, $fieldTypeIdentifier );

    /**
     * instanciates a field definition update class
     * @return FieldDefinitionUpdate
     */
    public function newFieldDefinitionUpdate();

}
