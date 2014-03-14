<?php
/**
 * File containing the eZ\Publish\Core\Repository\Permission\ContentTypeService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository\Permission
 */

namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct as APIContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Repository\Permission\PermissionsService;

/**
 * @example Examples/contenttype.php
 *
 * @package eZ\Publish\Core\Repository\Permission
 */
class ContentTypeService implements ContentTypeServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $permissionsService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $innerContentTypeService;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $innerContentTypeService
     * @param PermissionsService $permissionsService
     */
    public function __construct(
        ContentTypeServiceInterface $innerContentTypeService,
        PermissionsService $permissionsService
    )
    {
        $this->innerContentTypeService = $innerContentTypeService;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Create a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a group with the same identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function createContentTypeGroup( ContentTypeGroupCreateStruct  $contentTypeGroupCreateStruct )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'create' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'create' );

        if ( $contentTypeGroupCreateStruct->creatorId === null )
        {
            $contentTypeGroupCreateStruct->creatorId = $this->permissionsService->getCurrentUser()->id;
        }

        return $this->innerContentTypeService->createContentTypeGroup( $contentTypeGroupCreateStruct );
    }

    /**
     * Get a Content Type Group object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param mixed $contentTypeGroupId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup( $contentTypeGroupId )
    {
        return $this->innerContentTypeService->loadContentTypeGroup(
            $contentTypeGroupId
        );
    }

    /**
     * Get a Content Type Group object by identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param string $contentTypeGroupIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroupByIdentifier( $contentTypeGroupIdentifier )
    {
        return $this->innerContentTypeService->loadContentTypeGroupByIdentifier(
            $contentTypeGroupIdentifier
        );
    }

    /**
     * Get all Content Type Groups
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function loadContentTypeGroups()
    {
        return $this->innerContentTypeService->loadContentTypeGroups();
    }

    /**
     * Update a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier (if set) already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function updateContentTypeGroup( APIContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        if ( $contentTypeGroupUpdateStruct->modifierId === null )
        {
            $contentTypeGroupUpdateStruct->modifierId = $this->permissionsService->getCurrentUser()->id;
        }

        return $this->innerContentTypeService->updateContentTypeGroup(
            $contentTypeGroup,
            $contentTypeGroupUpdateStruct
        );
    }

    /**
     * Delete a Content Type Group.
     *
     * This method only deletes an content type group which has content types without any content instances
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If  a to be deleted content type has instances
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function deleteContentTypeGroup( APIContentTypeGroup $contentTypeGroup )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'delete' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'delete' );

        return $this->innerContentTypeService->deleteContentTypeGroup(
            $contentTypeGroup
        );
    }

    /**
     * Create a Content Type object.
     *
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException In case when
     *         - array of content type groups does not contain at least one content type group
     *         - identifier or remoteId in the content type create struct already exists
     *         - there is a duplicate field identifier in the content type create struct
     *         - content type create struct does not contain at least one field definition create struct
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     *         if a field definition in the $contentTypeCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeValidationException
     *         if a multiple field definitions of a same singular type are given
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType( APIContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'create' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'create' );

        if ( $contentTypeCreateStruct->creatorId === null )
        {
            $contentTypeCreateStruct->creatorId = $this->permissionsService->getCurrentUser()->id;
        }
        else if ( $contentTypeCreateStruct->creatorId !== $this->permissionsService->getCurrentUser()->id )
        {
            // Check that current user has read access to user set as creator
            $this->userService->loadUser( $contentTypeCreateStruct->creatorId );
        }

        return $this->innerContentTypeService->createContentType(
            $contentTypeCreateStruct,
            $contentTypeGroups
        );
    }

    /**
     * Get a Content Type object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If a content type with the given id and status DEFINED can not be found
     *
     * @param mixed $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentType( $contentTypeId )
    {
        return $this->innerContentTypeService->loadContentType(
            $contentTypeId
        );
    }

    /**
     * Get a Content Type object by identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given identifier and status DEFINED can not be found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier( $identifier )
    {
        return $this->innerContentTypeService->loadContentTypeByIdentifier(
            $identifier
        );
    }

    /**
     * Get a Content Type object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given remote id and status DEFINED can not be found
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId( $remoteId )
    {
        return $this->innerContentTypeService->loadContentTypeByRemoteId(
            $remoteId
        );
    }

    /**
     * Get a Content Type object draft by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param mixed $contentTypeId
     *
     * @todo Use another exception when user of draft is someone else
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function loadContentTypeDraft( $contentTypeId )
    {
        $contentTypeDraft = $this->innerContentTypeService->loadContentTypeDraft(
            $contentTypeId
        );

        if ( $contentTypeDraft->modifierId != $this->permissionsService->getCurrentUser()->id )
        {
            throw new NotFoundException( "ContentType owned by someone else", $contentTypeId );
        }

        return $contentTypeDraft;
    }

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[] Which have status DEFINED
     */
    public function loadContentTypes( APIContentTypeGroup $contentTypeGroup )
    {
        return $this->innerContentTypeService->loadContentTypes(
            $contentTypeGroup
        );
    }

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
     * @param \eZ\Publish\API\Repository\Values\User\User $modifier If null the current-user is used instead
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentTypeDraft( APIContentType $contentType, User $modifier = null )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'create' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'create' );

        if ( $modifier === null )
        {
            $modifier = $this->permissionsService->getCurrentUser();
        }

        return $this->innerContentTypeService->createContentTypeDraft(
            $contentType,
            $modifier
        );
    }

    /**
     * Update a Content Type object
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier or remoteId already exists
     *         or there is no draft assigned to the authenticated user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function updateContentTypeDraft( APIContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
        {
            throw new UnauthorizedException( 'ContentType', 'update' );
        }

        if ( $contentTypeUpdateStruct->modifierId === null )
        {
            $contentTypeUpdateStruct->modifierId = $this->permissionsService->getCurrentUser()->id;
        }

        return $this->innerContentTypeService->updateContentTypeDraft(
            $contentTypeDraft,
            $contentTypeUpdateStruct
        );
    }

    /**
     * Delete a Content Type object.
     *
     * Deletes a content type if it has no instances
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there exist content objects of this type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     */
    public function deleteContentType( APIContentType $contentType )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'delete' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'delete' );

        return $this->innerContentTypeService->deleteContentType(
            $contentType
        );
    }

    /**
     * Copy Type incl fields and groupIds to a new Type object
     *
     * New Type will have $creator as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current-user is not allowed to copy a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\User\User $creator if null the current-user is used
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function copyContentType( APIContentType $contentType, User $creator = null )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'create' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'create' );

        if ( empty( $creator ) )
        {
            $creator = $this->permissionsService->getCurrentUser();
        }

        return $this->innerContentTypeService->copyContentType(
            $contentType,
            $creator
        );
    }

    /**
     * Assigns a content type to a content type group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type is already assigned the given group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function assignContentTypeGroup( APIContentType $contentType, APIContentTypeGroup $contentTypeGroup )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        return $this->innerContentTypeService->assignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );
    }

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
    public function unassignContentTypeGroup( APIContentType $contentType, APIContentTypeGroup $contentTypeGroup )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        return $this->innerContentTypeService->unassignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );
    }

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
    public function addFieldDefinition( APIContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        return $this->innerContentTypeService->addFieldDefinition(
            $contentTypeDraft,
            $fieldDefinitionCreateStruct
        );

    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given field definition does not belong to the given type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function removeFieldDefinition( APIContentTypeDraft $contentTypeDraft, APIFieldDefinition $fieldDefinition )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        return $this->innerContentTypeService->removeFieldDefinition(
            $contentTypeDraft,
            $fieldDefinition
        );
    }

    /**
     * Update a field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     *                                                                        If the given identifier is used in an existing field of the given content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition( APIContentTypeDraft $contentTypeDraft, APIFieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        return $this->innerContentTypeService->updateFieldDefinition(
            $contentTypeDraft,
            $fieldDefinition,
            $fieldDefinitionUpdateStruct
        );
    }

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
    public function publishContentTypeDraft( APIContentTypeDraft $contentTypeDraft )
    {
        if ( $this->permissionsService->hasAccess( 'class', 'update' ) !== true )
        {
            throw new UnauthorizedException( 'ContentType', 'update' );
        }

        return $this->innerContentTypeService->publishContentTypeDraft(
            $contentTypeDraft
        );
    }

    /**
     * Instantiates a new content type group create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct( $identifier )
    {
        return $this->innerContentTypeService->newContentTypeGroupCreateStruct(
            $identifier
        );
    }

    /**
     * Instantiates a new content type create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct( $identifier )
    {
        return $this->innerContentTypeService->newContentTypeCreateStruct(
            $identifier
        );
    }

    /**
     * Instantiates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct()
    {
        return $this->innerContentTypeService->newContentTypeUpdateStruct();
    }

    /**
     * Instantiates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct()
    {
        return $this->innerContentTypeService->newContentTypeGroupUpdateStruct();
    }

    /**
     * Instantiates a field definition create struct
     *
     * @param string $fieldTypeIdentifier the required field type identifier
     * @param string $identifier the required identifier for the field definition
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct( $identifier, $fieldTypeIdentifier )
    {
        return $this->innerContentTypeService->newFieldDefinitionCreateStruct(
            $identifier,
            $fieldTypeIdentifier
        );
    }

    /**
     * Instantiates a field definition update class
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct()
    {
        return $this->innerContentTypeService->newFieldDefinitionUpdateStruct();
    }
}
