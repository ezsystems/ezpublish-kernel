<?php
/**
 * File containing the Content Type Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\Content\Type\Group;

/**
 */
interface Handler
{
    /**
     * @param \ezp\Persistence\Content\Type\Group\CreateStruct $group
     * @return \ezp\Persistence\Content\Type\Group
     */
    public function createGroup( GroupCreateStruct $group );

    /**
     * @param \ezp\Persistence\Content\Type\Group\UpdateStruct $group
     */
    public function updateGroup( GroupUpdateStruct $group );

    /**
     * @param mixed $groupId
     * @todo Throw exception if group is not found, also if group contains types
     */
    public function deleteGroup( $groupId );

    /**
     * @param int $groupId
     * @return \ezp\Persistence\Content\Type\Group
     */
    public function loadGroup( $groupId );

    /**
     * @return \ezp\Persistence\Content\Type\Group[]
     */
    public function loadAllGroups();

    /**
     * @param mixed $groupId
     * @param int $version ContentType version
     * @return \ezp\Persistence\Content\Type[]
     */
    public function loadContentTypes( $groupId, $version = 0 );

    /**
     * Load a content type by id and version
     *
     * @todo Use constant for $version?
     * @param int $contentTypeId
     * @param int $version
     * @return \ezp\Persistence\Content\Type
     */
    public function load( $contentTypeId, $version = 0 );

    /**
     * @param \ezp\Persistence\Content\Type\CreateStruct $contentType
     * @return \ezp\Persistence\Content\Type
     */
    public function create( CreateStruct $contentType );

    /**
     * @param mixed $typeId
     * @param int $version
     * @param \ezp\Persistence\Content\Type\UpdateStruct $contentType
     */
    public function update( $typeId, $version, UpdateStruct $contentType );

    /**
     * @param mixed $contentTypeId
     * @param int $version
     */
    public function delete( $contentTypeId, $version );

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $fromVersion
     * @param int $toVersion
     * @todo What does this method do? Create a new version of the content type 
     *       from $version? Is it then expected to return the Type object?
     */
    public function createVersion( $userId, $contentTypeId, $fromVersion, $toVersion );

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $version
     * @return Type
     * @todo What does this method do? Create a new Content\Type as a copy? 
     *       With which data (e.g. identified)?
     */
    public function copy( $userId, $contentTypeId, $version );

    /**
     * Unlink a content type group from a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $version 0|1
     * @throws \ezp\Base\Exception\NotFound If group or type is not found
     * @throws \ezp\Base\Exception\BadRequest If type is not part of group or group is last on type (delete type instead)
     */
    public function unlink( $groupId, $contentTypeId, $version );

    /**
     * Link a content type group with a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $version
     * @throws \ezp\Base\Exception\NotFound If group or type is not found
     * @throws \ezp\Base\Exception\BadRequest If type is already part of group
     */
    public function link( $groupId, $contentTypeId, $version );

    /**
     * Adds a new field definition to an existing Type.
     *
     * This method creates a new version of the Type with the $fieldDefinition 
     * added. It does not update existing content objects depending on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $version
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function addFieldDefinition( $contentTypeId, $version, FieldDefinition $fieldDefinition );

    /**
     * Removes a field definition from an existing Type.
     *
     * This method creates a new version of the Type with the field definition 
     * referred to by $fieldDefinitionId removed. It does not update existing 
     * content objects depending on the field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $version
     * @param mixed $fieldDefinitionId
     * @return boolean
     */
    public function removeFieldDefinition( $contentTypeId, $version, $fieldDefinitionId );

    /**
     * This method updates the given $fieldDefinition on a Type.
     *
     * This method creates a new version of the Type with the updated 
     * $fieldDefinition. It does not update existing content objects depending 
     * on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param int $version
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function updateFieldDefinition( $contentTypeId, $version, FieldDefinition $fieldDefinition );

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
     * @param int $version
     * @return void
     * @todo Is it correct that this refers to a $fieldDefinitionId instead of 
     *       a $typeId?
     */
    public function updateContentObjects( $contentTypeId, $version, $fieldDefinitionId );
}
?>
