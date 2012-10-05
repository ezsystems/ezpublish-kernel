<?php
/**
 * ContentTypeService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;

/**
 * ContentTypeService class
 * @package eZ\Publish\Core\SignalSlot
 */
class ContentTypeService implements ContentTypeServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( ContentTypeServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
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
    public function createContentTypeGroup( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct )
    {
        $returnValue = $this->service->createContentTypeGroup( $contentTypeGroupCreateStruct );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\CreateContentTypeGroupSignal( array(
                'groupId' => $returnValue->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Get a Content Type Group object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param int $contentTypeGroupId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup( $contentTypeGroupId )
    {
        $returnValue = $this->service->loadContentTypeGroup( $contentTypeGroupId );
        return $returnValue;
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
        $returnValue = $this->service->loadContentTypeGroupByIdentifier( $contentTypeGroupIdentifier );
        return $returnValue;
    }

    /**
     * Get all Content Type Groups
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function loadContentTypeGroups()
    {
        $returnValue = $this->service->loadContentTypeGroups();
        return $returnValue;
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
    public function updateContentTypeGroup( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup, \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct )
    {
        $returnValue = $this->service->updateContentTypeGroup( $contentTypeGroup, $contentTypeGroupUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\UpdateContentTypeGroupSignal( array(
                'contentTypeGroupId' => $contentTypeGroup->id,
            ) )
        );
        return $returnValue;
    }

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
    public function deleteContentTypeGroup( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup )
    {
        $returnValue = $this->service->deleteContentTypeGroup( $contentTypeGroup );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\DeleteContentTypeGroupSignal( array(
                'contentTypeGroupId' => $contentTypeGroup->id,
            ) )
        );
        return $returnValue;
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
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups Required array of
     *        {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups )
    {
        $returnValue = $this->service->createContentType( $contentTypeCreateStruct, $contentTypeGroups );
        $this->signalDispatcher->emit(
            new Signal\ContentTypeService\CreateContentTypeSignal( array(
                'contentTypeId' => $returnValue->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Get a Content Type object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If a content type with the given id and status DEFINED can not be found
     *
     * @param int $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentType( $contentTypeId )
    {
        $returnValue = $this->service->loadContentType( $contentTypeId );
        return $returnValue;
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
        $returnValue = $this->service->loadContentTypeByIdentifier( $identifier );
        return $returnValue;
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
        $returnValue = $this->service->loadContentTypeByRemoteId( $remoteId );
        return $returnValue;
    }

    /**
     * Get a Content Type object draft by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param int $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function loadContentTypeDraft( $contentTypeId )
    {
        $returnValue = $this->service->loadContentTypeDraft( $contentTypeId );
        return $returnValue;
    }

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[] an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup )
    {
        $returnValue = $this->service->loadContentTypes( $contentTypeGroup );
        return $returnValue;
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
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentTypeDraft( \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType )
    {
        $returnValue = $this->service->createContentTypeDraft( $contentType );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\CreateContentTypeDraftSignal( array(
                'contentTypeId' => $contentType->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Update a Content Type object
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier or remoteId already exists.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function updateContentTypeDraft( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft, \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct )
    {
        $returnValue = $this->service->updateContentTypeDraft( $contentTypeDraft, $contentTypeUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\UpdateContentTypeDraftSignal( array(
                'contentTypeDraftId' => $contentTypeDraft->id,
            ) )
        );
        return $returnValue;
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
    public function deleteContentType( \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType )
    {
        $returnValue = $this->service->deleteContentType( $contentType );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\DeleteContentTypeSignal( array(
                'contentTypeId' => $contentType->id,
            ) )
        );
        return $returnValue;
    }

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
    public function copyContentType( \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType, \eZ\Publish\API\Repository\Values\User\User $user = null )
    {
        $returnValue = $this->service->copyContentType( $contentType, $user );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\CopyContentTypeSignal( array(
                'contentTypeId' => $contentType->id,
                'userId' => $user->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * assign a content type to a content type group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type is already assigned the given group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function assignContentTypeGroup( \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType, \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup )
    {
        $returnValue = $this->service->assignContentTypeGroup( $contentType, $contentTypeGroup );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\AssignContentTypeGroupSignal( array(
                'contentTypeId' => $contentType->id,
                'contentTypeGroupId' => $contentTypeGroup->id,
            ) )
        );
        return $returnValue;
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
    public function unassignContentTypeGroup( \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType, \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup )
    {
        $returnValue = $this->service->unassignContentTypeGroup( $contentType, $contentTypeGroup );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\UnassignContentTypeGroupSignal( array(
                'contentTypeId' => $contentType->id,
                'contentTypeGroupId' => $contentTypeGroup->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Adds a new field definition to an existing content type.
     *
     * The content type must be in state DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the identifier in already exists in the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function addFieldDefinition( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft, \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        $returnValue = $this->service->addFieldDefinition( $contentTypeDraft, $fieldDefinitionCreateStruct );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\AddFieldDefinitionSignal( array(
                'contentTypeDraftId' => $contentTypeDraft->id,
            ) )
        );
        return $returnValue;
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
    public function removeFieldDefinition( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft, \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition )
    {
        $returnValue = $this->service->removeFieldDefinition( $contentTypeDraft, $fieldDefinition );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\RemoveFieldDefinitionSignal( array(
                'contentTypeDraftId' => $contentTypeDraft->id,
                'fieldDefinitionId' => $fieldDefinition->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Update a field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type of
     *                                                                        If the given identifier is used in an existing field of the given content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft, \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition, \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct )
    {
        $returnValue = $this->service->updateFieldDefinition( $contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\UpdateFieldDefinitionSignal( array(
                'contentTypeDraftId' => $contentTypeDraft->id,
                'fieldDefinitionId' => $fieldDefinition->id,
            ) )
        );
        return $returnValue;
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
    public function publishContentTypeDraft( \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft )
    {
        $returnValue = $this->service->publishContentTypeDraft( $contentTypeDraft );
        $this->signalDispatcher()->emit(
            new Signal\ContentTypeService\PublishContentTypeDraftSignal( array(
                'contentTypeDraftId' => $contentTypeDraft->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * instanciates a new content type group create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct( $identifier )
    {
        $returnValue = $this->service->newContentTypeGroupCreateStruct( $identifier );
        return $returnValue;
    }

    /**
     * instanciates a new content type create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct( $identifier )
    {
        $returnValue = $this->service->newContentTypeCreateStruct( $identifier );
        return $returnValue;
    }

    /**
     * Instantiates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct()
    {
        $returnValue = $this->service->newContentTypeUpdateStruct();
        return $returnValue;
    }

    /**
     * instanciates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct()
    {
        $returnValue = $this->service->newContentTypeGroupUpdateStruct();
        return $returnValue;
    }

    /**
     * instanciates a field definition create struct
     *
     * @param string $fieldTypeIdentifier the required  field type identifier
     * @param string $identifier the required identifier for the field definition
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct( $identifier, $fieldTypeIdentifier )
    {
        $returnValue = $this->service->newFieldDefinitionCreateStruct( $identifier, $fieldTypeIdentifier );
        return $returnValue;
    }

    /**
     * instanciates a field definition update class
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct()
    {
        $returnValue = $this->service->newFieldDefinitionUpdateStruct();
        return $returnValue;
    }

}

