<?php
/**
 * File containing the ContentTypeHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Type\Interfaces;
use \ezp\Persistence\Content\Type;

/**
 */
interface ContentTypeHandler
{
    /**
     * @param Group $group
     * @return Group
     */
    public function createGroup( Type\Group $group );

    /**
     * @todo: Add an update struct, which excludes the contentTypes property from the Group struct
     * @param Group $group
     */
    public function updateGroup( Type\Group $group );

    /**
     * @param mixed $groupId
     */
    public function deleteGroup( $groupId );

    /**
     * @return Type\Group[]
     */
    public function loadAllGroups();

    /**
     * @param mixed $groupId
     * @return Type[]
     */
    public function loadContentTypes( $groupId );

    /**
     * @param int $contentTypeId
     * @param int $version
     * @todo Use constant for $version?
     */
    public function load( $contentTypeId, $version = 1 );

    /**
     * @param Type\ContentTypeCreateStruct $contentType
     * @return Type
     */
    public function create( Type\ContentTypeCreateStruct $contentType );

    /**
     * @param Type\ContentTypeUpdateStruct $contentType
     */
    public function update( Type\ContentTypeUpdateStruct $contentType );

    /**
     * @param mixed $contentTypeId
     */
    public function delete( $contentTypeId );

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $version
     * @todo What does this method do? Create a new version of the content type 
     *       from $version? Is it then expected to return the Type object?
     */
    public function createVersion( $userId, $contentTypeId, $version );

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @return Type
     * @todo What does this method do? Create a new Content\Type as a copy? 
     *       With which data (e.g. identified)?
     */
    public function copy( $userId, $contentTypeId );

    /**
     * Unlink a content type group from a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     */
    public function unlink( $groupId, $contentTypeId );

    /**
     * Link a content type group with a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     */
    public function link( $groupId, $contentTypeId );

    /**
     * @param int $contentTypeId
     * @param int $groupId
     * @todo Isn't this the same as {@link link()}?
     */
    public function addGroup( $contentTypeId, $groupId );

    /**
     * Adds a new field definition to an existing Type.
     *
     * This method creates a new version of the Type with the $fieldDefinition 
     * added. It does not update existing content objects depending on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param Type\FieldDefinition $fieldDefinition
     * @return void
     */
    public function addFieldDefinition( $contentTypeId, Type\FieldDefinition $fieldDefinition );

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
    public function removeFieldDefinition( $contentTypeId, $fieldDefinitionId );

    /**
     * This method updates the given $fieldDefinition on a Type.
     *
     * This method creates a new version of the Type with the updated 
     * $fieldDefinition. It does not update existing content objects depending 
     * on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param Type\FieldDefinition $fieldDefinition
     * @return void
     */
    public function updateFieldDefinition( $contentTypeId, Type\FieldDefinition $fieldDefinition );

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
    public function updateContentObjects( $contentTypeId, $fieldDefinitionId );
}
?>
