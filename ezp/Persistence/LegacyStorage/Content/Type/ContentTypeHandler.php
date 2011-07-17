<?php
/**
 * File containing the ContentTypeHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\Interfaces,
    ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\ContentTypeUpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group;

/**
 */
class ContentTypeHandler implements Interfaces\ContentTypeHandler
{
    /**
     * ContentTypeGateway
     *
     * @var mixed
     */
    protected $contentTypeGateway;

    /**
     * Creates a new content type handler.
     *
     * @param ContentTypeGateway $contentTypeGateway
     */
    public function __construct( ContentTypeGateway $contentTypeGateway )
    {
        $this->contentTypeGateway = $contentTypeGateway;
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function createGroup( Group $group )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @todo: Add an update struct, which excludes the contentTypes property from the Group struct
     * @param Group $group
     */
    public function updateGroup( Group $group )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param mixed $groupId
     */
    public function deleteGroup( $groupId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @return Group[]
     */
    public function loadAllGroups()
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param mixed $groupId
     * @return Type[]
     */
    public function loadContentTypes( $groupId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param int $contentTypeId
     * @param int $version
     * @todo Use constant for $version?
     */
    public function load( $contentTypeId, $version = 1 )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param ContentTypeCreateStruct $contentType
     * @return Type
     * @todo Refactor. Testing way too expensive.
     */
    public function create( ContentTypeCreateStruct $createStruct )
    {
        $createStruct = clone $createStruct;
        $contentType  = new Type();

        $contentType->id = $this->contentTypeGateway->insertType(
            $createStruct
        );
        foreach ( $createStruct->contentTypeGroupIds as $groupId )
        {
            $this->contentTypeGateway->insertGroupAssignement(
                $contentType->id,
                $groupId
            );
        }
        foreach ( $createStruct->fieldDefinitions as $fieldDef )
        {
            $fieldDef->id = $this->contentTypeGateway->insertFieldDefinition(
                $contentType->id,
                $fieldDef
            );
        }
        $this->typeFromCreateStruct( $contentType, $createStruct );
        return $contentType;
    }

    /**
     * Copies attributes from $createStruct to $type.
     *
     * @param Type $type
     * @param ContentTypeCreateStruct $createStruct
     */
    protected function typeFromCreateStruct( Type $type, ContentTypeCreateStruct $createStruct )
    {
        $type->name                = $createStruct->name;
        $type->description         = $createStruct->description;
        $type->identifier          = $createStruct->identifier;
        $type->created             = $createStruct->created;
        $type->modified            = $createStruct->modified;
        $type->creatorId           = $createStruct->creatorId;
        $type->modifierId          = $createStruct->modifierId;
        $type->remoteId            = $createStruct->remoteId;
        $type->urlAliasSchema      = $createStruct->urlAliasSchema;
        $type->nameSchema          = $createStruct->nameSchema;
        $type->isContainer         = $createStruct->isContainer;
        $type->initialLanguageId   = $createStruct->initialLanguageId;
        $type->contentTypeGroupIds = $createStruct->contentTypeGroupIds;
        $type->fieldDefinitions    = $createStruct->fieldDefinitions;
    }

    /**
     * @param Type\ContentTypeUpdateStruct $contentType
     */
    public function update( ContentTypeUpdateStruct $contentType )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param mixed $contentTypeId
     */
    public function delete( $contentTypeId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @param int $version
     * @todo What does this method do? Create a new version of the content type
     *       from $version? Is it then expected to return the Type object?
     */
    public function createVersion( $userId, $contentTypeId, $version )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param mixed $userId
     * @param mixed $contentTypeId
     * @return Type
     * @todo What does this method do? Create a new Content\Type as a copy?
     *       With which data (e.g. identified)?
     */
    public function copy( $userId, $contentTypeId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * Unlink a content type group from a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     */
    public function unlink( $groupId, $contentTypeId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * Link a content type group with a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     */
    public function link( $groupId, $contentTypeId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }

    /**
     * @param int $contentTypeId
     * @param int $groupId
     * @todo Isn't this the same as {@link link()}?
     */
    public function addGroup( $contentTypeId, $groupId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
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
    public function addFieldDefinition( $contentTypeId, FieldDefinition $fieldDefinition )
    {
        throw new \RuntimeException( "Not implemented, yet." );
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
    public function removeFieldDefinition( $contentTypeId, $fieldDefinitionId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
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
    public function updateFieldDefinition( $contentTypeId, FieldDefinition $fieldDefinition )
    {
        throw new \RuntimeException( "Not implemented, yet." );
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
    public function updateContentObjects( $contentTypeId, $fieldDefinitionId )
    {
        throw new \RuntimeException( "Not implemented, yet." );
    }
}
?>
