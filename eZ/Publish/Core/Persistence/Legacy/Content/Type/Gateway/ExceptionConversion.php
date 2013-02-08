<?php
/**
 * File containing the Content Type Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

/**
 * Base class for content type gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Inserts the given $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group $group
     *
     * @return mixed Group ID
     */
    public function insertGroup( Group $group )
    {
        try
        {
            return $this->innerGateway->insertGroup( $group );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates a group with data in $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct $group
     *
     * @return void
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        try
        {
            return $this->innerGateway->updateGroup( $group );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns the number of types in a certain group.
     *
     * @param int $groupId
     *
     * @return int
     */
    public function countTypesInGroup( $groupId )
    {
        try
        {
            return $this->innerGateway->countTypesInGroup( $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns the number of Groups the type is assigned to.
     *
     * @param int $typeId
     * @param int $status
     *
     * @return int
     */
    public function countGroupsForType( $typeId, $status )
    {
        try
        {
            return $this->innerGateway->countGroupsForType( $typeId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the Group with the given $groupId.
     *
     * @param int $groupId
     *
     * @return void
     */
    public function deleteGroup( $groupId )
    {
        try
        {
            return $this->innerGateway->deleteGroup( $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns an array with data about the Group with $groupId.
     *
     * @param int $groupId
     *
     * @return array
     */
    public function loadGroupData( $groupId )
    {
        try
        {
            return $this->innerGateway->loadGroupData( $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns an array with data about the Group with $identifier.
     *
     * @param int $identifier
     *
     * @return array
     */
    public function loadGroupDataByIdentifier( $identifier )
    {
        try
        {
            return $this->innerGateway->loadGroupDataByIdentifier( $identifier );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns an array with data about all Group objects.
     *
     * @return array
     */
    public function loadAllGroupsData()
    {
        try
        {
            return $this->innerGateway->loadAllGroupsData();
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for all Types in $status in $groupId.
     *
     * @param mixed $groupId
     * @param int $status
     *
     * @return string[][]
     */
    public function loadTypesDataForGroup( $groupId, $status )
    {
        try
        {
            return $this->innerGateway->loadTypesDataForGroup( $groupId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a new content type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     * @param mixed|null $typeId
     *
     * @return mixed Type ID
     */
    public function insertType( Type $type, $typeId = null )
    {
        try
        {
            return $this->innerGateway->insertType( $type, $typeId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Insert assignment of $typeId to $groupId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param mixed $groupId
     *
     * @return void
     */
    public function insertGroupAssignment( $typeId, $status, $groupId )
    {
        try
        {
            return $this->innerGateway->insertGroupAssignment( $typeId, $status, $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes a group assignments for a Type.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteGroupAssignment( $groupId, $typeId, $status )
    {
        try
        {
            return $this->innerGateway->deleteGroupAssignment( $groupId, $typeId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads an array with data about field definition referred $id and $status.
     *
     * @param mixed $id field definition id
     * @param int $status field definition status
     *
     * @return array Data rows.
     */
    public function loadFieldDefinition( $id, $status )
    {
        try
        {
            return $this->innerGateway->loadFieldDefinition( $id, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     *
     * @return mixed Field definition ID
     */
    public function insertFieldDefinition(
        $typeId, $status, FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    )
    {
        try
        {
            return $this->innerGateway->insertFieldDefinition( $typeId, $status, $fieldDefinition, $storageFieldDef );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes a field definition.
     *
     * @param mixed $typeId
     * @param int $status
     * @param mixed $fieldDefinitionId
     *
     * @return void
     */
    public function deleteFieldDefinition( $typeId, $status, $fieldDefinitionId )
    {
        try
        {
            return $this->innerGateway->deleteFieldDefinition( $typeId, $status, $fieldDefinitionId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     *
     * @return void
     */
    public function updateFieldDefinition(
        $typeId, $status, FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    )
    {
        try
        {
            return $this->innerGateway->updateFieldDefinition( $typeId, $status, $fieldDefinition, $storageFieldDef );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Update a type with $updateStruct.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $updateStruct
     *
     * @return void
     */
    public function updateType( $typeId, $status, UpdateStruct $updateStruct )
    {
        try
        {
            return $this->innerGateway->updateType( $typeId, $status, $updateStruct );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads an array with data about $typeId in $status.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return array Data rows.
     */
    public function loadTypeData( $typeId, $status )
    {
        try
        {
            return $this->innerGateway->loadTypeData( $typeId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads an array with data about the type referred to by $identifier in
     * $status.
     *
     * @param string $identifier
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeDataByIdentifier( $identifier, $status )
    {
        try
        {
            return $this->innerGateway->loadTypeDataByIdentifier( $identifier, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads an array with data about the type referred to by $remoteId in
     * $status.
     *
     * @param mixed $remoteId
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeDataByRemoteId( $remoteId, $status )
    {
        try
        {
            return $this->innerGateway->loadTypeDataByRemoteId( $remoteId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Counts the number of instances that exists of the identified type.
     *
     * @param int $typeId
     *
     * @return int
     */
    public function countInstancesOfType( $typeId )
    {
        try
        {
            return $this->innerGateway->countInstancesOfType( $typeId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes a Type completely.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function delete( $typeId, $status )
    {
        try
        {
            return $this->innerGateway->delete( $typeId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes all field definitions of a Type.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteFieldDefinitionsForType( $typeId, $status )
    {
        try
        {
            return $this->innerGateway->deleteFieldDefinitionsForType( $typeId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes a the Type.
     *
     * Does no delete the field definitions!
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteType( $typeId, $status )
    {
        try
        {
            return $this->innerGateway->deleteType( $typeId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes all group assignments for a Type.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return void
     */
    public function deleteGroupAssignmentsForType( $typeId, $status )
    {
        try
        {
            return $this->innerGateway->deleteGroupAssignmentsForType( $typeId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Publishes the Type with $typeId from $sourceVersion to $targetVersion,
     * including its fields
     *
     * @param int $typeId
     * @param int $sourceStatus
     * @param int $targetStatus
     *
     * @return void
     */
    public function publishTypeAndFields( $typeId, $sourceStatus, $targetStatus )
    {
        try
        {
            return $this->innerGateway->publishTypeAndFields( $typeId, $sourceStatus, $targetStatus );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
