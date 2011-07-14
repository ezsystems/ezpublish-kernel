<?php
/**
 * File containing the ContentTypeHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_content_type
 */

namespace ezp\Persistence\Content\Type\Interfaces;

/**
 * @package ezp
 * @subpackage persistence_content_type
 */
interface ContentTypeHandler
{
    /**
     * @param Group $group
     * @return Group
     */
    public function createGroup( \ezp\Persistence\Content\Type\Group $group );

    /**
     * @todo: Add an update struct, which excludes the contentTypes property from the Group struct
     * @param Group $group
     */
    public function updateGroup( \ezp\Persistence\Content\Type\Group $group );

    /**
     * @param int $groupId
     */
    public function deleteGroup( $groupId );

    /**
     * @return array
     */
    public function loadAllGroups();

    /**
     * @param int $groupId
     * @return array
     */
    public function loadContentTypes( $groupId );

    /**
     * @param int $contentTypeId
     * @param int $version
     * @todo Use constant for $version?
     */
    public function load( $contentTypeId, $version = 1 );

    /**
     * @param Type $contentType
     * @return Type
     */
    public function create( \ezp\Persistence\Content\Type $contentType );

    /**
     * @todo: Add an update struct, which excludes the fieldDefinitions and contentTypeGroupIDs property from the Group struct
     * @param Type $contentType
     */
    public function update( \ezp\Persistence\Content\Type $contentType );

    /**
     * @param int $contentTypeId
     */
    public function delete( $contentTypeId );

    /**
     * @param int $userId
     * @param int $contentTypeId
     * @param int $version
     */
    public function createVersion( $userId, $contentTypeId, $version );

    /**
     * @param int $userId
     * @param int $contentTypeId
     * @return Type
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
     */
    public function addGroup($contentTypeId, $groupId);

    /**
     * addFieldDefinition
     *
     *
     *
     * This method does not update existing content objects depending on the
     * field (default) values.
     *
     * @param mixed $contentTypeId
     * @param Type\FieldDefinition $fieldDefinition
     * @return void
     */
    public function addFieldDefinition( $contentTypeId, Type\FieldDefinition $fieldDefinition );

    /**
     * Removes a field definition
     *
     * @param mixed $contentTypeId
     * @param mixed $fieldDefinitionId
     * @return boolean
     */
    public function removeFieldDefinition( $contentTypeId, $fieldDefinitionId );

    /**
     * This method does not update existing content objects depending on the
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
     */
    public function updateContentObjects( $contentTypeId, $fieldDefinitionId );
}
?>
