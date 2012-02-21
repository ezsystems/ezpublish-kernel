<?php
/**
 * File containing the ContentTypeServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\ContentTypeService;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;

use eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeGroupStub;
use eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeStub;
use eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeDraftStub;
use eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeCreateStructStub;
use eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionCreateStructStub;
use eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub;

/**
 * @example Examples/contenttype.php
 *
 * @package eZ\Publish\API\Repository
 */
class ContentTypeServiceStub implements ContentTypeService
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    private $groups = array();

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    private $groupsById = array();

    /**
     * @var int
     */
    private $nextGroupId = 0;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType[]
     */
    private $types = array();

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft[]
     */
    private $typeDrafts = array();

    /**
     * @var int
     */
    private $nextTypeId = 0;

    /**
     * Properties of a ContentTypeGroup
     *
     * @var string[]
     */
    private $groupProperties;

    /**
     *
     */
    public function __construct()
    {
        $this->initGroupProperties();
    }

    /**
     * Initialize array of reflected group properties
     *
     * @return void
     */
    protected function initGroupProperties()
    {
        $this->groupProperties = array();

        $reflectionClass = new \ReflectionClass(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup'
        );

        foreach ( $reflectionClass->getProperties() as $reflectionProperty )
        {
            $this->groupProperties[] = $reflectionProperty->name;
        }
        $this->groupProperties[] = 'names';
        $this->groupProperties[] = 'descriptions';
    }

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
    public function createContentTypeGroup( ContentTypeGroupCreateStruct  $contentTypeGroupCreateStruct )
    {
        $data = array();
        foreach ( $this->groupProperties as $propertyName )
        {
            if ( isset( $contentTypeGroupCreateStruct->$propertyName ) )
            {
                $data[$propertyName] = $contentTypeGroupCreateStruct->$propertyName;
            }
        }

        $data['id'] = $this->nextGroupId++;

        $group = new ContentTypeGroupStub( $data );

        $this->setGroup( $group );

        return $group;
    }

    /**
     * Sets the group internally
     *
     * @param \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeGroupStub $group
     * @return void
     */
    protected function setGroup( ContentTypeGroupStub $group )
    {
        if ( isset( $this->groups[$group->identifier] ) )
        {
            throw new Exceptions\IllegalArgumentExceptionStub;
        }
        $this->groups[$group->identifier] = $group;
        $this->groupsById[$group->id] = $group;
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
        if ( !isset( $this->groupsById[$contentTypeGroupId] ) )
        {
            throw new Exceptions\NotFoundExceptionStub;
        }
        return $this->groupsById[$contentTypeGroupId];
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
        if ( !isset( $this->groups[$contentTypeGroupIdentifier] ) )
        {
            throw new Exceptions\NotFoundExceptionStub;
        }
        return $this->groups[$contentTypeGroupIdentifier];
    }

    /**
     * Get all Content Type Groups
     *
     * @return aray an array of {@link ContentTypeGroup}
     */
    public function loadContentTypeGroups()
    {
        return $this->groupsById;
    }

    /**
     * Update a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given identifier (if set) already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function updateContentTypeGroup( ContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct )
    {
        unset( $this->groups[$contentTypeGroup->identifier] );
        unset( $this->groupsById[$contentTypeGroup->id] );

        $data = array();

        foreach ( $this->groupProperties as $propertyName )
        {
            if ( isset( $contentTypeGroup->$propertyName ) )
            {
                $data[$propertyName] = $contentTypeGroup->$propertyName;
            }
            if ( isset( $contentTypeGroupUpdateStruct->$propertyName ) )
            {
                $data[$propertyName] = $contentTypeGroupUpdateStruct->$propertyName;
            }
        }

        $newGroup = new ContentTypeGroupStub( $data );

        $this->setGroup( $newGroup );
    }

    /**
     * Delete a Content Type Group. 
     * 
     * This method only deletes an content type group which has content types without any content instances
     * 
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If  a to be deleted content type has instances
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function deleteContentTypeGroup( ContentTypeGroup $contentTypeGroup )
    {
        unset( $this->groups[$contentTypeGroup->identifier] );
        unset( $this->groupsById[$contentTypeGroup->id] );
    }

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
    public function createContentType( ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups )
    {
        $this->checkContentTypeCreate( $contentTypeCreateStruct );

        $data = array();
        foreach ( $contentTypeCreateStruct as $propertyName => $propertyValue )
        {
            $data[$propertyName] = $propertyValue;
        }

        $data['fieldDefinitions'] = array();

        if ( is_array( $contentTypeCreateStruct->fieldDefinitions ) )
        {
            $fieldDefinitionCreates = $contentTypeCreateStruct->fieldDefinitions;
            foreach ( $fieldDefinitionCreates as $fieldDefinitionCreate )
            {
                $data['fieldDefinitions'][] = $this->createFieldDefinition( $fieldDefinitionCreate );
            }
        }

        $data['contentTypeGroups'] = $contentTypeGroups;

        // FIXME: Set status to draft
        $data['id'] = $this->nextTypeId++;

        $type = new ContentTypeDraftStub( new ContentTypeStub( $data ) );

        $this->typeDrafts[$type->id] = $type;

        return $type;
    }

    /**
     * Checks that the given $contentTypeCreateStruct is valid
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the identifier or remoteId in the content type create struct already exists
     *         or there is a dublicate field identifier
     *
     * @param ContentTypeCreateStruct $contentTypeCreateStruct
     * @return void
     */
    protected function checkContentTypeCreate( ContentTypeCreateStruct $contentTypeCreateStruct )
    {
        $types = array_merge( $this->types, $this->typeDrafts );
        foreach ( $types as $type )
        {
            if ( $type->identifier == $contentTypeCreateStruct->identifier )
            {
                throw new Exceptions\IllegalArgumentExceptionStub;
            }
            if ( $type->remoteId == $contentTypeCreateStruct->remoteId )
            {
                throw new Exceptions\IllegalArgumentExceptionStub;
            }
        }

        $fieldIdentifiers = array();
        foreach ( $contentTypeCreateStruct->fieldDefinitions as $fieldDefinitionCreate )
        {
            if ( isset( $fieldIdentifiers[$fieldDefinitionCreate->identifier] ) )
            {
                throw new Exceptions\IllegalArgumentExceptionStub;
            }
            $fieldIdentifiers[$fieldDefinitionCreate->identifier] = true;
        }
    }

    /**
     * Creates a FieldDefinition from $fieldDefinitionCreate
     *
     * @param FieldDefinitionCreateStruct $fieldDefinitionCreate
     * @return FieldDefinition
     */
    protected function createFieldDefinition( FieldDefinitionCreateStruct $fieldDefinitionCreate )
    {
        $data = array();
        foreach ( $fieldDefinitionCreate as $propertyName => $propertyValue )
        {
            $data[$propertyName] = $propertyValue;
        }

        // FIXME: Work around inconsistency
        if ( isset( $data['fieldTypeIdentifier'] ) )
        {
            $data['fieldType'] = $data['fieldTypeIdentifier'];
            unset( $data['fieldTypeIdentifier'] );
        }

        return new FieldDefinitionStub( $data );
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
        if ( isset( $this->typeDrafts[$contentTypeId] ) )
        {
            return $this->typeDrafts[$contentTypeId];
        }
    }

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
    public function updateContentTypeDraft( ContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct )
    {
        $data = array(
            'id'                     => $contentTypeDraft->id,
            'status'                 => $contentTypeDraft->status,
            'names'                  => $contentTypeDraft->names,
            'descriptions'           => $contentTypeDraft->descriptions,
            'identifier'             => $contentTypeDraft->identifier,
            'creationDate'           => $contentTypeDraft->creationDate,
            'modificationDate'       => $contentTypeDraft->modificationDate,
            'creatorId'              => $contentTypeDraft->creatorId,
            'modifierId'             => $contentTypeDraft->modifierId,
            'remoteId'               => $contentTypeDraft->remoteId,
            'urlAliasSchema'         => $contentTypeDraft->urlAliasSchema,
            'nameSchema'             => $contentTypeDraft->nameSchema,
            'isContainer'            => $contentTypeDraft->isContainer,
            'mainLanguageCode'       => $contentTypeDraft->mainLanguageCode,
            'defaultAlwaysAvailable' => $contentTypeDraft->defaultAlwaysAvailable,
            'defaultSortField'       => $contentTypeDraft->defaultSortField,
            'defaultSortOrder'       => $contentTypeDraft->defaultSortOrder,
            'contentTypeGroups'      => $contentTypeDraft->contentTypeGroups,
            'fieldDefinitions'       => $contentTypeDraft->fieldDefinitions,
        );

        foreach ( array_keys( $data ) as $propertyName )
        {
            if ( isset( $contentTypeUpdateStruct->$propertyName ) )
            {
                $data[$propertyName] = $contentTypeUpdateStruct->$propertyName;
            }
        }

        $this->typeDrafts[$contentTypeDraft->id] = new ContentTypeDraftStub(
            new ContentTypeStub( $data )
        );
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
        // TODO: Implement.
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
        // TODO: Implement.
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
        // TODO: Implement.
    }

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     *
     * @return array an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes( ContentTypeGroup $contentTypeGroup )
    {
        // TODO: Implement.
    }

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
    public function createContentTypeDraft( ContentType $contentType )
    {
        // TODO: Implement.
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
    public function deleteContentType( ContentType $contentType )
    {
        // TODO: Implement.
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
    public function copyContentType( ContentType $contentType, User $user = null )
    {
        // TODO: Implement.
    }

    /**
     * assign a content type to a content type group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the content type is already assigned the given group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function assignContentTypeGroup( ContentType $contentType, ContentTypeGroup $contentTypeGroup )
    {
        // TODO: Implement.
    }

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
    public function unassignContentTypeGroup( ContentType $contentType, ContentTypeGroup $contentTypeGroup )
    {
        // TODO: Implement.
    }

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
    public function addFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        // TODO: Implement.
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given field definition does not belong to the given type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function removeFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition )
    {
        // TODO: Implement.
    }

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
    public function updateFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct )
    {
        // TODO: Implement.
    }

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
    public function publishContentTypeDraft( ContentTypeDraft $contentTypeDraft )
    {
        // TODO: Implement.
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
        $groupCreate = new ContentTypeGroupCreateStruct();
        $groupCreate->identifier = $identifier;
        return $groupCreate;
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
        $typeCreate = new ContentTypeCreateStructStub();
        $typeCreate->identifier = $identifier;
        return $typeCreate;
    }

    /**
     * instanciates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct()
    {
        return new ContentTypeUpdateStruct();
    }

    /**
     * instanciates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct()
    {
        return new ContentTypeGroupUpdateStruct();
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
        $fieldDefinitionCreate =  new FieldDefinitionCreateStruct();

        $fieldDefinitionCreate->identifier          = $identifier;
        $fieldDefinitionCreate->fieldTypeIdentifier = $fieldTypeIdentifier;

        return $fieldDefinitionCreate;
    }

    /**
     * instanciates a field definition update class
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct()
    {
        // TODO: Implement.
    }

}
