<?php
/**
 * File containing the Content Type Handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Type;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldGroup\CreateStruct as FieldGroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldGroup\UpdateStruct as FieldGroupUpdateStruct;

/**
 */
interface Handler
{
    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct $group
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group
     */
    public function createGroup( GroupCreateStruct $group );

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct $group
     */
    public function updateGroup( GroupUpdateStruct $group );

    /**
     * deletes a content type group
     *
     * the caller ensures that the group exists and contains no type
     *
     * @param mixed $groupId
     */
    public function deleteGroup( $groupId );

    /**
     * @param mixed $groupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type group with id is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group
     */
    public function loadGroup( $groupId );

    /**
     * Loads Type Group by identifier
     *
     * Legacy note: Uses name for identifier.
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type group with id is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group
     */
    public function loadGroupByIdentifier( $identifier );

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group[]
     */
    public function loadAllGroups();

    /**
     * @param mixed $groupId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type[]
     */
    public function loadContentTypes( $groupId, $status = Type::STATUS_DEFINED );

    /**
     * Loads a content type by id and status
     *
     * Note: This method is responsible of having the Field Definitions of the loaded ContentType sorted by placement.
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type with provided status is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function load( $contentTypeId, $status = Type::STATUS_DEFINED );

    /**
     * Loads a (defined) content type by identifier
     *
     * Note: This method is responsible of having the Field Definitions of the loaded ContentType sorted by placement.
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If defined type is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function loadByIdentifier( $identifier );

    /**
     * Loads a (defined) content type by remote id
     *
     * Note: This method is responsible of having the Field Definitions of the loaded ContentType sorted by placement.
     *
     * @param mixed $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If defined type is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function loadByRemoteId( $remoteId );

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct $contentType
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function create( CreateStruct $contentType );

    /**
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @param \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $contentType
     */
    public function update( $contentTypeId, $status, UpdateStruct $contentType );

    /**
     * deletes a content type
     *
     * the caller ensures that the content type exists and if it has status DEFINED there are no content
     * objects with this type.
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public function delete( $contentTypeId, $status );

    /**
     * Creates a draft of existing defined content type
     *
     * Updates modified date, sets $modifierId and status to Type::STATUS_DRAFT on the new returned draft.
     * The caller ensures that the content type with $contentTypeId exists and has status DRAFT and there is no
     * other DRAFT.
     *
     * @param mixed $modifierId
     * @param mixed $contentTypeId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function createDraft( $modifierId, $contentTypeId );

    /**
     * Copy a Type incl fields and group-relations from a given status to a new Type with status {@link Type::STATUS_DRAFT}
     *
     * New Content Type will have $userId as creator / modifier, created / modified should be updated, new remoteId created
     * and identifier should be 'copy_of_<identifier>_' + the new remoteId or another unique number.
     * The caller ensures that the user and the given content type with the given status exist.
     *
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function copy( $userId, $contentTypeId, $status );

    /**
     * Unlink a content type group from a content type.
     *
     * The caller ensures that the content type and the group exist and the
     * content type group is not the only group of the content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public function unlink( $groupId, $contentTypeId, $status );

    /**
     * Link a content type group with a content type
     *
     * The caller ensures, that the content type and the group exist and the type is not already part of the group
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public function link( $groupId, $contentTypeId, $status );

    /**
     * Returns field definition for the given field definition id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If field definition is not found
     *
     * @param mixed $id
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function getFieldDefinition( $id, $status );

    /**
     * Counts the number of Content instances of the ContentType identified by given $contentTypeId.
     *
     * @param mixed $contentTypeId
     *
     * @return int
     */
    public function getContentCount( $contentTypeId );

    /**
     * Adds a new field definition to an existing Type.
     *
     * This method creates a new version of the Type with the $fieldDefinition
     * added. It does not update existing content objects depending on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     * @todo Add FieldDefinition\CreateStruct?
     */
    public function addFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition );

    /**
     * Removes a field definition from an existing Type.
     *
     * This method creates a new version of the Type with the field definition
     * referred to by $fieldDefinitionId removed. It does not update existing
     * content objects depending on the field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @param mixed $fieldDefinitionId
     *
     * @return void
     */
    public function removeFieldDefinition( $contentTypeId, $status, $fieldDefinitionId );

    /**
     * This method updates the given $fieldDefinition on an existing Type.
     *
     * This method creates a new version of the Type with the updated
     * $fieldDefinition. It does not update existing content objects depending
     * on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return void
     * @todo Add FieldDefinition\UpdateStruct?
     */
    public function updateFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition );

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
     * The caller ensures that the content type with status DRAFT exists
     *
     * @param mixed $contentTypeId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If type with $contentTypeId and Type::STATUS_DRAFT is not found
     *
     * @return void
     */
    public function publish( $contentTypeId );

    /**
     * creates a new field group
     *
     * @param FieldGroupCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldGroup
     */
    public function createFieldGroup( FieldGroupCreateStruct $createStruct );

    /**
     * loads all existing field groups
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldGroup[]
     */
    public function loadFieldGroups();

    /**
     * loads a field group for the given $id
     *
     * @param $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the field group with the give $id is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldGroup
     */
    public function loadFieldGroup( $id );

    /**
     * loads a field group for the given $identifier
     *
     * @param $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the field group with the give $identifier is not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldGroup
     */
    public function loadFieldGroupByIdentifier( $identifier );

    /**
     * updates the given field group
     *
     * @param mixed $id
     * @param FieldGroupUpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldGroup
     */
    public function updateFieldGroup( $id, FieldGroupUpdateStruct $updateStruct );
}
