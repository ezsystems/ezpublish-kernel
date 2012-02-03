<?php
/**
 * File containing the Content Type Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type,
    eZ\Publish\SPI\Persistence\Content\Type\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\Group;

/**
 */
interface Handler
{
    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct $group
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group
     */
    public function createGroup( GroupCreateStruct $group );

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct $group
     */
    public function updateGroup( GroupUpdateStruct $group );

    /**
     * @param mixed $groupId
     * @todo Throw exception if group is not found, also if group contains types
     */
    public function deleteGroup( $groupId );

    /**
     * @param mixed $groupId
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group
     */
    public function loadGroup( $groupId );

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group[]
     */
    public function loadAllGroups();

    /**
     * @param mixed $groupId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @return \eZ\Publish\SPI\Persistence\Content\Type[]
     */
    public function loadContentTypes( $groupId, $status = Type::STATUS_DEFINED );

    /**
     * Load a content type by id and status
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     * @throws \ezp\Base\Exception\NotFound If type with provided status is not found
     */
    public function load( $contentTypeId, $status = Type::STATUS_DEFINED );

    /**
     * Load a (defined) content type by identifier
     *
     * @param string $identifier
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     * @throws \ezp\Base\Exception\NotFound If defined type is not found
     */
    public function loadByIdentifier( $identifier );

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct $contentType
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
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public function delete( $contentTypeId, $status );

    /**
     * Creates a draft of existing defined content type
     *
     * Updates modified date, sets $modifierId and status to Type::STATUS_DRAFT on the new returned draft.
     *
     * @param mixed $modifierId
     * @param mixed $contentTypeId
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     * @throws \ezp\Base\Exception\NotFound If type with defined status is not found
     */
    public function createDraft( $modifierId, $contentTypeId );

    /**
     * Copy a Type incl fields and group-relations from a given status to a new Type with status {@link Type::STATUS_DRAFT}
     *
     * New Content Type will have $userId as creator / modifier, created / modified should be updated, new remoteId created
     * and identifier should be appended with '_' + the new remoteId or another unique number.
     *
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     * @throws \ezp\Base\Exception\NotFound If user or type with provided status is not found
     */
    public function copy( $userId, $contentTypeId, $status );

    /**
     * Unlink a content type group from a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @throws \ezp\Base\Exception\NotFound If group or type with provided status is not found
     * @throws \ezp\Base\Exception\BadRequest If type is not part of group or group is last on type (delete type instead)
     */
    public function unlink( $groupId, $contentTypeId, $status );

    /**
     * Link a content type group with a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @throws \ezp\Base\Exception\NotFound If group or type with provided status is not found
     * @throws \ezp\Base\Exception\BadRequest If type is already part of group
     */
    public function link( $groupId, $contentTypeId, $status );

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
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     * @throws \ezp\Base\Exception\NotFound If type is not found
     * @todo Add FieldDefintion\CreateStruct?
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
     * @return void
     * @throws \ezp\Base\Exception\NotFound If field is not found
     */
    public function removeFieldDefinition( $contentTypeId, $status, $fieldDefinitionId );

    /**
     * This method updates the given $fieldDefinition on a Type.
     *
     * This method creates a new version of the Type with the updated
     * $fieldDefinition. It does not update existing content objects depending
     * on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @return void
     * @throws \ezp\Base\Exception\NotFound If field is not found
     * @todo Add FieldDefintion\UpdateStruct?
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
     * @param mixed $contentTypeId
     * @return void
     * @throws \ezp\Base\Exception\NotFound If type with $contentTypeId and Type::STATUS_DRAFT is not found
     */
    public function publish( $contentTypeId );
}
