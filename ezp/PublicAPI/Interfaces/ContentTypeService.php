<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\ContentType\FieldDefinitionUpdateStruct;
use ezp\PublicAPI\Values\ContentType\FieldDefinition;
use ezp\PublicAPI\Values\ContentType\FieldDefinitionCreateStruct;
use ezp\PublicAPI\Values\ContentType\ContentType;
use ezp\PublicAPI\Values\ContentType\ContentTypeDraft;
use ezp\PublicAPI\Values\ContentType\ContentTypeGroup;
use ezp\PublicAPI\Values\User\User;
use ezp\Values\ContentType\ContentTypeUpdateStruct;
use ezp\PublicAPI\Values\Content\ContentType\ContentTypeCreateStruct;
use ezp\PublicAPI\Values\ContentType\ContentTypeGroupUpdateStruct;
use ezp\VPublicAPI\alues\ContentType\ContentTypeGroupCreateStruct;

/**
 * @example Examples/contenttype.php
 * 
 * @package ezp\PublicAPI\Interfaces
 */
interface ContentTypeService
{
    /**
     * Create a Content Type Group object
     *
     * @param ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct
     * 
     * @return ContentTypeGroup
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to create a content type group
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If a group with the same identifier already exists
     */
    public function createContentTypeGroup(/*ContentTypeGroupCreateStruct*/  $contentTypeGroupCreateStruct );

    /**
     * Get a Content Type Group object by id
     *
     * @param int $contentTypeGroupId
     * 
     * @return ContentTypeGroup
     * 
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If group can not be found
     */
    public function loadContentTypeGroup( $contentTypeGroupId );

    /**
     * Get a Content Type Group object by identifier
     *
     * @param string $contentTypeGroupIdentifier
     * 
     * @return ContentTypeGroup
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If group can not be found
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
     * @param ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to create a content type group
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the given identifier (if set) already exists
     */
    public function updateContentTypeGroup(/*ContentTypeGroup*/ $contentTypeGroup, /*ContentTypeGroupStruct*/ $contentTypeGroupUpdateStruct );

    /**
     * Delete a Content Type Group. If the paramter $deleteObjects is set to true
     * this method deletes also all content types in this group which
     * are not assigned to other groups including the content object instances.
     *
     * @param ContentTypeGroup
     * @param boolean $deleteObjects indicates if content object should be deleted if exist
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to delete a content type group
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the parameter $deleteObjects is set to false and a to be deleted content type
     *                                       has instances
     */
    public function deleteContentTypeGroup( /*ContentTypeGroup*/ $contentTypeGroup, $deleteObjects = false );

    /**
     * Create a Content Type object. The content type is created in the state STATUS_DRAFT.
     *
     * @param ContentTypeCreateStruct $contentTypeCreateStruct
     * @param array $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     * 
     * @return ContentTypeDraft
     * 
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the identifier or remoteId in the content type create struct already exists
     *         or there is a dublicate field identifier
     */
    public function createContentType( /*ContentTypeCreateStruct*/ $contentTypeCreateStruct, array $contentTypeGroups );

    /**
     * Get a Content Type object by id
     *
     * @param int $contentTypeId
     * 
     * @return ContentType
     * 
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If a content type with the given id and status DEFINED can not be found
     */
    public function loadContentType( $contentTypeId );

    /**
     * Get a Content Type object by identifier
     *
     * @param string $identifier
     * 
     * @return ContentType
     * 
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If content type with the given identifier and status DEFINED can not be found
     */
    public function loadContentTypeByIdentifier( $identifier );

    /**
     * Get a Content Type object by id
     *
     * @param string $remoteId
     * 
     * @return ContentType
     * 
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If content type with the given remote id and status DEFINED can not be found
     */
    public function loadContentTypeByRemoteId( $remoteId );


    /**
     * Get a Content Type object draft by id
     *
     * @param int $contentTypeId
     * 
     * @return ContentTypeDraft
     * 
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If the content type draft owned by the current user can not be found
     */
    public function loadContentTypeDraft( $contentTypeId );

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param ContentTypeGroup $contentTypeGroup
     * 
     * @return array an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes(/*ContentTypeGroup*/ $contentTypeGroup );
     
    /**
     * Creates a draft from an existing content type. This is a complete copy of the content
     * type wiich has the state STATUS_DRAFT.
     * 
     * @param ContentType $contentType
     * 
     * @return ContentTypeDraft
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to edit a content type
     * @throws ezp\PublicAPI\Interfaces\BadStateException If there is already a draft assigned to another user
     */
    public function createContentTypeDraft(/*ContentType*/ $contentType);


    /**
     * Update a Content Type object
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * @param ContentTypeUpdateStruct $contentTypeUpdateStruct
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to update a content type
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the given identifier or remoteId already exists or there is no draft assigned to the authenticated user
     */
    public function updateContentTypeDraft(/*ContentTypeDraft*/ $contentTypeDraft, /*ContentTypeUpdateStruct*/ $contentTypeUpdateStruct );

    /**
     * Delete a Content Type object. If $deleteObjects is set to true all object instances of this content type are deleted.
     *
     * @param ContentType $contentType
     * @param boolean $deleteObjects indicates if content object should be deleted if exist
     * 
     * @throws ezp\PublicAPI\Interfaces\BadStateException $deleteObjects is set to false and there exist content objects of this type
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to delete a content type
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
     * 
     * @return ContentType
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to copy a content type
     */
    public function copyContentType(/*ContentType*/ $contentType, /*User*/ $user = null );

    /**
     * assign a content type to a content type group.
     *
     * @param ContentType $contentType
     * @param ContentTypeGroup $contentTypeGroup
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the content type is already assigned the given group
     */
    public function assignContentTypeGroup( /*ContentType*/ $contentType, /*ContentTypeGroup*/ $contentTypeGroup );

    /**
     * Unassign a content type from a group.
     *
     * @param ContentType $contentType
     * @param ContentTypeGroup $contentTypeGroup
     * 
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to link a content type
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the content type is not assigned this the given group.
     * @throws ezp\PublicAPI\Interfaces\BadStateException If $contentTypeGroup is the last group assigned to the content type
     */
    public function unassignContentTypeGroup( /*ContentType*/ $contentType, /*ContentTypeGroup*/ $contentTypeGroup );

    /**
     * Adds a new field definition to an existing content type. The content type must be in state DRAFT.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * @param FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     * 
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException if the identifier in already exists in the content type
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to edit a content type
     */
    public function addFieldDefinition( /*ContentTypeDraft*/ $contentTypeDraft, /*FieldDefinitionCreateStruct*/ $fieldDefinitionCreateStruct  );

    /**
     * Remove a field definition from an existing Type.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * @param FieldDefinition $fieldDefinition
     * 
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException If the given field definition does not belong to the given type
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to edit a content type
     */
    public function removeFieldDefinition( /*ContentTypeDraft*/ $contentTypeDraft, /*FieldDefinition*/ $fieldDefinition  );

    /**
     * Update a field definition
     *
     * @param ContentTypeDraft $contentTypeDraft the content type draft
     * @param FieldDefinition $fieldDefinition the field definition which should be updated
     * @param FieldDefinitionUpdateStruct $fieldDefinitionStruct
     * 
     * @throws InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to edit a content type
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException  If the given identifier is used in an existing field of the given content type
     */
    public function updateFieldDefinition( /*ContentTypeDraft*/ $contentTypeDraft, /*FieldDefinition*/ $fieldDefinition, /*FieldDefinitionUpdateStruct*/ $fieldDefinitionUpdateStruct  );

    /**
     * Publish the content type and update content objects.
     *
     * This method updates content objects, depending on the changed field definitions.
     *
     * @param ContentTypeDraft $contentTypeDraft
     * 
     * @throws ezp\PublicAPI\Interfaces\BadStateException If the content type has no draft
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to publish a content type
     */
    public function publishContentTypeDraft( /*ContentType*/ $contentTypeDraft  );

    /**
     * instanciates a new content type group create class
     * 
     * @param string $identifier
     * 
     * @return ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct($identifier);

    /**
     * instanciates a new content type create class
     * 
     * @param string $identifier
     * 
     * @return ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct($identifier);

    /**
     * instanciates a new content type update struct
     * 
     * @return ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct();

    /**
     * instanciates a new content type update struct
     * 
     * @return ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct();

    /**
     * instanciates a field definition create struct
     * 
     * @param string $fieldTypeIdentifier the required  field type identifier
     * @param string $identifier the required identifier for the field definition
     * 
     * @return FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier );

    /**
     * instanciates a field definition update class
     * 
     * @return FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct();

}
