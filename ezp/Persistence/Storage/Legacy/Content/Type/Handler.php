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
    ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater,
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
     * Content updater
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater
     */
    protected $contentUpdater;

    /**
     * Creates a new content type handler.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $contentTypeGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Mapper $mapper
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater $contentUpdater
     */
    public function __construct(
        Gateway $contentTypeGateway,
        Mapper $mapper,
        ContentUpdater $contentUpdater )
    {
        $this->contentTypeGateway = $contentTypeGateway;
        $this->mapper = $mapper;
        $this->contentUpdater = $contentUpdater;
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
     */
    public function load( $contentTypeId, $status = Type::STATUS_DEFINED )
    {
        $rows = $this->contentTypeGateway->loadTypeData(
            $contentTypeId, $status
        );

        $types = $this->mapper->extractTypesFromRows( $rows );

        return $types[0];
    }

    /**
     * @param \ezp\Persistence\Content\Type\CreateStruct $contentType
     * @return Type
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
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $status
     * @todo Can be optimized in gateway?
     * @todo $userId becomes only $modifierId or also $creatorId?
     * @todo What about $modified and $created?
     */
    public function createVersion( $userId, $contentTypeId, $fromVersion, $toVersion )
    {
        $createStruct = $this->mapper->createCreateStructFromType(
            $this->load( $contentTypeId, $fromVersion )
        );
        $createStruct->status = $toVersion;
        $createStruct->modifierId = $userId;
        $createStruct->modified = time();

        return $this->create( $createStruct );
    }

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
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

        $actions = $this->contentUpdater->determineActions( $fromType, $toType );
        $this->contentUpdater->applyUpdates( $contentTypeId, $actions  );

        $this->contentTypeGateway->deleteType( $contentTypeId, 0 );
        $this->contentTypeGateway->deleteFieldDefinitionsForType(
            $contentTypeId, 0
        );
        $this->contentTypeGateway->publishTypeAndFields( $contentTypeId, 1 );
    }
}
?>
