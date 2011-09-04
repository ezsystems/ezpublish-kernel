<?php
/**
 * File containing the Content Type Gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition;

/**
 * Base class for content type gateways.
 */
abstract class Gateway
{
    /**
     * Inserts the given $group.
     *
     * @return mixed Group ID
     */
    abstract public function insertGroup( Group $group );

    /**
     * Updates a group with data in $group.
     *
     * @param \ezp\Persistence\Content\Type\Group\UpdateStruct $group
     * @return void
     */
    abstract public function updateGroup( GroupUpdateStruct $group );

    /**
     * Returns the number of types in a certain group.
     *
     * @param int $groupId
     * @return int
     */
    abstract public function countTypesInGroup( $groupId );

    /**
     * Returns the number of Groups the type is assigned to.
     *
     * @param int $typeId
     * @param int $status
     * @return int
     */
    abstract public function countGroupsForType( $typeId, $status );

    /**
     * Deletes the Group with the given $groupId.
     *
     * @param int $groupId
     * @return void
     */
    abstract public function deleteGroup( $groupId );

    /**
     * Returns an array with data about the Group with $groupId.
     *
     * @param int $groupId
     * @return array
     */
    abstract public function loadGroupData( $groupId );

    /**
     * Returns an array with data about all Group objects.
     *
     * @return array
     */
    abstract public function loadAllGroupsData();

    /**
     * Loads data for all Types in $status in $groupId.
     *
     * @param mixed $groupId
     * @param int $status
     * @return string[][]
     */
    abstract public function loadTypesDataForGroup( $groupId, $status );

    /**
     * Inserts a new conten type.
     *
     * @param Type $createStruct
     * @return mixed Type ID
     */
    abstract public function insertType( Type $type );

    /**
     * Insert assignement of $typeId to $groupId.
     *
     * @param mixed $typeId
     * @param int $version
     * @param mixed $groupId
     * @return void
     */
    abstract public function insertGroupAssignement( $typeId, $version, $groupId );

    /**
     * Deletes a group assignements for a Type.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $version
     * @return void
     */
    abstract public function deleteGroupAssignement( $groupId, $typeId, $version );

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $version
     * @param FieldDefinition $fieldDefinition
     * @param StorageFieldDefinition $storageFieldDef
     * @return mixed Field definition ID
     */
    abstract public function insertFieldDefinition(
        $typeId, $version, FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    );

    /**
     * Deletes a field definition.
     *
     * @param mixed $typeId
     * @param int $version
     * @param mixed $fieldDefinitionId
     * @return void
     */
    abstract public function deleteFieldDefinition( $typeId, $version, $fieldDefinitionId );

    /**
     * Updates a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $version
     * @param FieldDefinition $fieldDefinition
     * @param StorageFieldDefinition $storageFieldDef
     * @return void
     */
    abstract public function updateFieldDefinition(
        $typeId, $version, FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    );

    /**
     * Update a type with $updateStruct.
     *
     * @param mixed $type
     * @param int $version
     * @param \ezp\Persistence\Content\Type\UpdateStruct $updateStruct
     * @return void
     */
    abstract public function updateType( $typeId, $version, UpdateStruct $updateStruct );

    /**
     * Loads an array with data about $typeId in $version.
     *
     * @param mixed $typeId
     * @param int $version
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    abstract public function loadTypeData( $typeId, $version );

    /**
     * Counts the number of instances that exists of the identified type.
     *
     * @param int $typeId
     * @param int $version
     * @return int
     */
    abstract public function countInstancesOfType( $typeId, $version );

    /**
     * Deletes all field definitions of a Type.
     *
     * @param mixed $typeId
     * @return void
     */
    abstract public function deleteFieldDefinitionsForType( $typeId, $version );

    /**
     * Deletes a the Type.
     *
     * Does no delete the field definitions!
     *
     * @param mixed $typeId
     * @return void
     */
    abstract public function deleteType( $typeId, $version );

    /**
     * Deletes all group assignements for a Type.
     *
     * @param mixed $typeId
     * @return void
     */
    abstract public function deleteGroupAssignementsForType( $typeId, $version );

    /**
     * Publishes the Type with $typeId from $sourceVersion to 0, including its
     * fields
     *
     * @param int $typeId
     * @param int $sourceVersion
     * @return void
     */
    abstract public function publishTypeAndFields( $typeId, $sourceVersion );
}
