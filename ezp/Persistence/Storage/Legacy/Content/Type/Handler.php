<?php
/**
 * File containing the Content Type Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\Handler as BaseContentTypeHandler,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler as UpdateHandler,
    ezp\Persistence\Storage\Legacy\Exception;

/**
 */
class Handler implements BaseContentTypeHandler
{
    /**
     * ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     *
     * @var mixed
     */
    protected $contentTypeGateway;

    /**
     * Mappper for Type objects.
     *
     * @var Mapper
     */
    protected $mapper;

    /**
     * Content Type update handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler
     */
    protected $updateHandler;

    /**
     * Creates a new content type handler.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $contentTypeGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Mapper $mapper
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler $updateHandler
     */
    public function __construct(
        Gateway $contentTypeGateway,
        Mapper $mapper,
        UpdateHandler $updateHandler )
    {
        $this->contentTypeGateway = $contentTypeGateway;
        $this->mapper = $mapper;
        $this->updateHandler = $updateHandler;
    }

    /**
     * @param \ezp\Persistence\Content\Type\Group\CreateStruct $createStruct
     * @return Group
     */
    public function createGroup( GroupCreateStruct $createStruct )
    {
        $group = $this->mapper->createGroupFromCreateStruct(
            $createStruct
        );

        $group->id = $this->contentTypeGateway->insertGroup(
            $group
        );

        return $group;
    }

    /**
     * @param \ezp\Persistence\Content\Type\Group\UpdateStruct $struct
     * @return \ezp\Persistence\Content\Type\Group
     */
    public function updateGroup( GroupUpdateStruct $struct )
    {
        $this->contentTypeGateway->updateGroup(
            $struct
        );
        return $this->loadGroup( $struct->id );
    }

    /**
     * @param mixed $groupId
     * @throws \ezp\Persistence\Storage\Legacy\Exception\GroupNotEmpty
     *         if a non-empty group is to be deleted.
     */
    public function deleteGroup( $groupId )
    {
        if ( $this->contentTypeGateway->countTypesInGroup( $groupId ) !== 0 )
        {
            throw new Exception\GroupNotEmpty( $groupId );
        }
        $this->contentTypeGateway->deleteGroup( $groupId );
    }

    /**
     * @param int $groupId
     * @return Group
     */
    public function loadGroup( $groupId )
    {
        $rows = $this->contentTypeGateway->loadGroupData( $groupId );
        $groups = $this->mapper->extractGroupsFromRows( $rows );

        return $groups[0];
    }

    /**
     * @return Group[]
     */
    public function loadAllGroups()
    {
        $rows = $this->contentTypeGateway->loadAllGroupsData();
        return $this->mapper->extractGroupsFromRows( $rows );
    }

    /**
     * @param mixed $groupId
     * @param int $status
     * @return Type[]
     */
    public function loadContentTypes( $groupId, $status = 0 )
    {
        $rows = $this->contentTypeGateway->loadTypesDataForGroup( $groupId, $status );
        return $this->mapper->extractTypesFromRows( $rows );
    }

    /**
     * @param int $contentTypeId
     * @param int $status
     * @return \ezp\Persistence\Content\Type
     */
    public function load( $contentTypeId, $status = Type::STATUS_DEFINED )
    {
        $rows = $this->contentTypeGateway->loadTypeData(
            $contentTypeId, $status
        );

        $types = $this->mapper->extractTypesFromRows( $rows );

        if ( count( $types ) !== 1 )
        {
            throw new Exception\TypeNotFound( $contentTypeId, $status );
        }

        return $types[0];
    }

    /**
     * Load a (defined) content type by identifier
     *
     * @param string $identifier
     * @return \ezp\Persistence\Content\Type
     * @throws \ezp\Base\Exception\NotFound If defined type is not found
     */
    public function loadByIdentifier( $identifier )
    {
        throw new \RuntimeException( "@TODO Implement" );
    }

    /**
     * @param \ezp\Persistence\Content\Type\CreateStruct $createStruct
     * @return \ezp\Persistence\Content\Type
     */
    public function create( CreateStruct $createStruct )
    {
        $createStruct = clone $createStruct;
        $contentType = $this->mapper->createTypeFromCreateStruct(
            $createStruct
        );

        $contentType->id = $this->contentTypeGateway->insertType(
            $contentType
        );
        foreach ( $contentType->groupIds as $groupId )
        {
            $this->contentTypeGateway->insertGroupAssignement(
                $groupId,
                $contentType->id,
                $contentType->status
            );
        }
        foreach ( $contentType->fieldDefinitions as $fieldDef )
        {
            $storageFieldDef = new StorageFieldDefinition();
            $this->mapper->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
            $fieldDef->id = $this->contentTypeGateway->insertFieldDefinition(
                $contentType->id,
                $contentType->status,
                $fieldDef,
                $storageFieldDef
            );
        }
        return $contentType;
    }

    /**
     * @param mixed $typeId
     * @param int $status
     * @param \ezp\Persistence\Content\Type\UpdateStruct $contentType
     * @return Type
     * @todo Maintain contentclass_name
     */
    public function update( $typeId, $status, UpdateStruct $contentType )
    {
        $this->contentTypeGateway->updateType(
            $typeId, $status, $contentType
        );
        return $this->load(
            $typeId, $status
        );
    }

    /**
     * @param mixed $contentTypeId
     * @todo Maintain contentclass_name
     */
    public function delete( $contentTypeId, $status )
    {
        $count = $this->contentTypeGateway->countInstancesOfType(
            $contentTypeId, $status
        );
        if ( $count > 0 )
        {
            throw new Exception\TypeStillHasContent( $contentTypeId, $status );
        }

        $this->contentTypeGateway->deleteGroupAssignementsForType(
            $contentTypeId, $status
        );
        $this->contentTypeGateway->deleteFieldDefinitionsForType(
            $contentTypeId, $status
        );
        $this->contentTypeGateway->deleteType(
            $contentTypeId, $status
        );

        // FIXME: Return true only if deletion happened
        return true;
    }

    /**
     * Creates a draft of existing defined content type
     *
     * Updates modified date, sets $modifierId and status to Type::STATUS_DRAFT on the new returned draft.
     *
     * @param mixed $modifierId
     * @param mixed $contentTypeId
     * @return \ezp\Persistence\Content\Type
     * @throws \ezp\Base\Exception\NotFound If type with defined status is not found
     * @todo Can be optimized in gateway?
     * @todo Should user be validated? And should it throw if there is an existing draft of content type??
     */
    public function createDraft( $modifierId, $contentTypeId )
    {
        $createStruct = $this->mapper->createCreateStructFromType(
            $this->load( $contentTypeId, Type::STATUS_DEFINED )
        );
        $createStruct->status = Type::STATUS_DRAFT;
        $createStruct->modifierId = $modifierId;
        $createStruct->modified = time();

        return $this->create( $createStruct );
    }

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @return Type
     * @todo Can be optimized in gateway?
     */
    public function copy( $userId, $contentTypeId, $status )
    {
        $createStruct = $this->mapper->createCreateStructFromType(
            $this->load( $contentTypeId, $status )
        );
        $createStruct->modifierId = $userId;
        $createStruct->modified = time();
        $createStruct->creatorId = $userId;
        $createStruct->created = time();

        return $this->create( $createStruct );
    }

    /**
     * Unlink a content type group from a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status
     */
    public function unlink( $groupId, $contentTypeId, $status )
    {
        $groupCount = $this->contentTypeGateway->countGroupsForType(
            $contentTypeId, $status
        );
        if ( $groupCount < 2 )
        {
            throw new Exception\RemoveLastGroupFromType(
                $contentTypeId, $status
            );
        }

        $this->contentTypeGateway->deleteGroupAssignement(
            $groupId, $contentTypeId, $status
        );
        // FIXME: What is to be returned?
        return true;
    }

    /**
     * Link a content type group with a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     */
    public function link( $groupId, $contentTypeId, $status )
    {
        $this->contentTypeGateway->insertGroupAssignement(
            $groupId, $contentTypeId, $status
        );
        // FIXME: What is to be returned?
        return true;
    }

    /**
     * Adds a new field definition to an existing Type.
     *
     * This method creates a new status of the Type with the $fieldDefinition
     * added. It does not update existing content objects depending on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function addFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition )
    {
        $storageFieldDef = new StorageFieldDefinition();
        $this->mapper->toStorageFieldDefinition(
            $fieldDefinition, $storageFieldDef
        );
        $fieldDefinition->id = $this->contentTypeGateway->insertFieldDefinition(
            $contentTypeId, $status, $fieldDefinition, $storageFieldDef
        );
    }

    /**
     * Removes a field definition from an existing Type.
     *
     * This method creates a new status of the Type with the field definition
     * referred to by $fieldDefinitionId removed. It does not update existing
     * content objects depending on the field (default) values.
     *
     * @param mixed $contentTypeId
     * @param mixed $fieldDefinitionId
     * @return boolean
     */
    public function removeFieldDefinition( $contentTypeId, $status, $fieldDefinitionId )
    {
        $this->contentTypeGateway->deleteFieldDefinition(
            $contentTypeId, $status, $fieldDefinitionId
        );
        // FIXME: Return true only if deletion happened
        return true;
    }

    /**
     * This method updates the given $fieldDefinition on a Type.
     *
     * This method creates a new status of the Type with the updated
     * $fieldDefinition. It does not update existing content objects depending
     * on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function updateFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition )
    {
        $storageFieldDef = new StorageFieldDefinition();
        $this->mapper->toStorageFieldDefinition(
            $fieldDefinition, $storageFieldDef
        );
        $this->contentTypeGateway->updateFieldDefinition(
            $contentTypeId, $status, $fieldDefinition, $storageFieldDef
        );
    }

    /**
     * Update content objects
     *
     * Updates content objects, depending on the changed field definitions.
     *
     * A content type has a state which tells if its content objects yet have
     * been adapted.
     *
     * Flags the content type as updated.
     *
     * @param mixed $contentTypeId
     * @return void
     */
    public function publish( $contentTypeId )
    {
        $fromType = $this->load( $contentTypeId, 0 );
        $toType   = $this->load( $contentTypeId, 1 );
        $this->updateHandler->performUpdate( $fromType, $toType );
    }
}
?>
