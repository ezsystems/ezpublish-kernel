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
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;

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
     * Creates a new content type handler.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $contentTypeGateway
     * @param Mapper $mapper
     */
    public function __construct( Gateway $contentTypeGateway, Mapper $mapper )
    {
        $this->contentTypeGateway = $contentTypeGateway;
        $this->mapper = $mapper;
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
     * @param \ezp\Persistence\Content\Type\Group\UpdateStruct $group
     * @return bool
     * @todo Should we return the Group here? Would require an additional
     *       SELECT, though.
     */
    public function updateGroup( GroupUpdateStruct $struct )
    {
        $this->contentTypeGateway->updateGroup(
            $struct
        );
        // FIXME: Determine if Group should be returned instead
        return true;
    }

    /**
     * @param mixed $groupId
     * @todo Is cascading (Group -> Type -> Content) intended?
     */
    public function deleteGroup( $groupId )
    {
        // Load type-version combinations which are not in any other group
        // Delete group assignement
        // Delete all types that have no more groups
        //   Delete all content objects of these types
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param int $groupId
     * @return Group
     */
    public function loadGroup( $groupId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @return Group[]
     * @todo This will result in basically all Type definitions (including
     *       all field definitions) to be loaded, since they are inherently
     *       loaded with a Group. Is this really intended?
     */
    public function loadAllGroups()
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param mixed $groupId
     * @return Type[]
     * @todo These are already present in the Group instance. Why load them
     *       dedicatedly here?
     */
    public function loadContentTypes( $groupId, $version = 0 )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param int $contentTypeId
     * @param int $version
     * @todo Use constant for $version?
     */
    public function load( $contentTypeId, $version = 0 )
    {
        $rows = $this->contentTypeGateway->loadTypeData(
            $contentTypeId, $version
        );

        $types = $this->mapper->extractTypesFromRows( $rows );

        return $types[0];
    }

    /**
     * @param \ezp\Persistence\Content\Type\CreateStruct $contentType
     * @return Type
     * @todo Maintain contentclass_name
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
        foreach ( $contentType->contentTypeGroupIds as $groupId )
        {
            $this->contentTypeGateway->insertGroupAssignement(
                $groupId,
                $contentType->id,
                $contentType->version
            );
        }
        foreach ( $contentType->fieldDefinitions as $fieldDef )
        {
            $fieldDef->id = $this->contentTypeGateway->insertFieldDefinition(
                $contentType->id,
                $contentType->version,
                $fieldDef
            );
        }
        return $contentType;
    }

    /**
     * @param mixed $typeId
     * @param int $version
     * @param \ezp\Persistence\Content\Type\UpdateStruct $contentType
     * @return Type
     * @todo Maintain contentclass_name
     */
    public function update( $typeId, $version, UpdateStruct $contentType )
    {
        $this->contentTypeGateway->updateType(
            $typeId, $version, $contentType
        );
        return $this->load(
            $typeId, $version
        );
    }

    /**
     * @param mixed $contentTypeId
     * @todo Needs to delete all content objects of that type, too.
     * @todo Maintain contentclass_name
     */
    public function delete( $contentTypeId, $version )
    {
        $this->contentTypeGateway->deleteGroupAssignementsForType(
            $contentTypeId, $version
        );
        $this->contentTypeGateway->deleteFieldDefinitionsForType(
            $contentTypeId, $version
        );
        $this->contentTypeGateway->deleteType(
            $contentTypeId, $version
        );

        // FIXME: Return true only if deletion happened
        return true;
    }

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $version
     * @todo Can be optimized in gateway?
     * @todo $userId becomes only $modifierId or also $creatorId?
     * @todo What about $modified and $created?
     */
    public function createVersion( $userId, $contentTypeId, $fromVersion, $toVersion )
    {
        $createStruct = $this->mapper->createCreateStructFromType(
            $this->load( $contentTypeId, $fromVersion )
        );
        $createStruct->version = $toVersion;
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
    public function copy( $userId, $contentTypeId, $version )
    {
        $createStruct = $this->mapper->createCreateStructFromType(
            $this->load( $contentTypeId, $version )
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
     * @param int $version
     * @param int $version
     * @todo Check if content type is in another group, otherwise delete?
     */
    public function unlink( $groupId, $contentTypeId, $version )
    {
        $this->contentTypeGateway->deleteGroupAssignement(
            $groupId, $contentTypeId, $version
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
    public function link( $groupId, $contentTypeId, $version )
    {
        $this->contentTypeGateway->insertGroupAssignement(
            $groupId, $contentTypeId, $version
        );
        // FIXME: What is to be returned?
        return true;
    }

    /**
     * Adds a new field definition to an existing Type.
     *
     * This method creates a new version of the Type with the $fieldDefinition
     * added. It does not update existing content objects depending on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function addFieldDefinition( $contentTypeId, $version, FieldDefinition $fieldDefinition )
    {
        $fieldDefinition->id = $this->contentTypeGateway->insertFieldDefinition(
            $contentTypeId, $version, $fieldDefinition
        );
    }

    /**
     * Removes a field definition from an existing Type.
     *
     * This method creates a new version of the Type with the field definition
     * referred to by $fieldDefinitionId removed. It does not update existing
     * content objects depending on the field (default) values.
     *
     * @param mixed $contentTypeId
     * @param mixed $fieldDefinitionId
     * @return boolean
     */
    public function removeFieldDefinition( $contentTypeId, $version, $fieldDefinitionId )
    {
        $this->contentTypeGateway->deleteFieldDefinition(
            $contentTypeId, $version, $fieldDefinitionId
        );
        // FIXME: Return true only if deletion happened
        return true;
    }

    /**
     * This method updates the given $fieldDefinition on a Type.
     *
     * This method creates a new version of the Type with the updated
     * $fieldDefinition. It does not update existing content objects depending
     * on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function updateFieldDefinition( $contentTypeId, $version, FieldDefinition $fieldDefinition )
    {
        $this->contentTypeGateway->updateFieldDefinition(
            $contentTypeId, $version, $fieldDefinition
        );
    }

    /**
     * Update content objects
     *
     * Updates content objects, depending on the changed field definition.
     *
     * A content type has a state which tells if its content objects yet have
     * been adapted.
     *
     * Flags the content type as updated.
     *
     * @param mixed $contentTypeId
     * @return void
     * @todo Is it correct that this refers to a $fieldDefinitionId instead of
     *       a $typeId?
     */
    public function updateContentObjects( $contentTypeId, $version, $fieldDefinitionId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }
}
?>
