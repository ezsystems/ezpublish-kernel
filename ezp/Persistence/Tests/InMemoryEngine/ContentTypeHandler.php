<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use ezp\Persistence\Content\Type\Handler as ContentTypeHandlerInterface,
    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\Content\Type\Group,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\BadRequest,
    RuntimeException;

/**
 * @see ezp\Persistence\Content\Type\Handler
 *
 * @status //autogentag//
 */
class ContentTypeHandler implements ContentTypeHandlerInterface
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @param \ezp\Persistence\Content\Type\Group\CreateStruct $group
     * @return \ezp\Persistence\Content\Type\Group
     */
    public function createGroup( GroupCreateStruct $group )
    {
        $groupArr = (array) $group;
        return $this->backend->create( 'Content\\Type\\Group', $groupArr );
    }

    /**
     * @param \ezp\Persistence\Content\Type\Group\UpdateStruct $group
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        $groupArr = (array) $group;
        $this->backend->update( 'Content\\Type\\Group', $groupArr['id'], $groupArr );
    }

    /**
     * @param mixed $groupId
     * @todo Throw exception if group is not found, also if group contains types
     */
    public function deleteGroup( $groupId )
    {
        $this->backend->delete( 'Content\\Type\\Group', $groupId );

        // Remove group id from content types
        $types = $this->backend->find( 'Content\\Type', array( 'groupIds' => $groupId ) );
        foreach ( $types as $type )
        {
            $update = false;
            foreach ( $type->groupIds as $key => $contentTypeGroupId )
            {
                if ( $contentTypeGroupId == $groupId )
                {
                    unset( $type->groupIds[$key] );
                    $update = true;
                }
            }

            if ( $update )
            {
                // @todo If groupIds is empty, content type and content of that type should be deleted?
                $this->backend->update( 'Content\\Type',
                                        $type->id,
                                        array( 'groupIds' => $type->groupIds ) );
            }
        }
    }

    /**
     * @param int $groupId
     * @return \ezp\Persistence\Content\Type\Group
     */
    public function loadGroup( $groupId )
    {
        return $this->backend->load( 'Content\\Type\\Group', $groupId );
    }

    /**
     * @return \ezp\Persistence\Content\Type\Group[]
     */
    public function loadAllGroups()
    {
        return $this->backend->find( 'Content\\Type\\Group', array() );
    }

    /**
     * @param mixed $groupId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @return \ezp\Persistence\Content\Type[]
     */
    public function loadContentTypes( $groupId, $status = 0 )
    {
        return $this->backend->find(
            'Content\\Type',
            array( 'groupIds' => $groupId, 'status' => $status ),
            array( 'fieldDefinitions' => array(
                'type' => 'Content\\Type\\FieldDefinition',
                'match' => array( '_typeId' => 'id',  '_status' => 'status' ) )
            )
        );
    }

    /**
     * Load a content type by id and version
     *
     * @param int $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @return \ezp\Persistence\Content\Type
     */
    public function load( $contentTypeId, $status = 0 )
    {
        $type = $this->backend->find(
            'Content\\Type',
            array( 'id' => $contentTypeId, 'status' => $status ),
            array( 'fieldDefinitions' => array(
                'type' => 'Content\\Type\\FieldDefinition',
                'match' => array( '_typeId' => 'id',  '_status' => 'status' ) )
            )
        );

        if ( !$type )
            return null;

        return $type[0];
    }

    /**
     * @param \ezp\Persistence\Content\Type\CreateStruct $contentType
     * @return \ezp\Persistence\Content\Type
     */
    public function create( CreateStruct $contentType )
    {
        $contentTypeArr = (array) $contentType;
        unset( $contentTypeArr['fieldDefinitions'] );
        $contentTypeObj = $this->backend->create( 'Content\\Type', $contentTypeArr );
        foreach ( $contentType->fieldDefinitions as $field )
        {
            $contentTypeObj->fieldDefinitions[] = $this->backend->create(
                'Content\\Type\\FieldDefinition',
                array( '_typeId' => $contentTypeObj->id, '_status' => $contentTypeObj->status ) + (array) $field
            );
        }
        return $contentTypeObj;
    }

    /**
     * @param mixed $typeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @param \ezp\Persistence\Content\Type\UpdateStruct $contentType
     */
    public function update( $typeId, $status, UpdateStruct $contentType )
    {
        $contentTypeArr = (array) $contentType;
        $this->backend->updateByMatch( 'Content\\Type',
                                       array( 'id' => $typeId, 'status' => $status ),
                                       $contentTypeArr );
    }

    /**
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public function delete( $contentTypeId, $status )
    {
        $this->backend->deleteByMatch( 'Content\\Type',
                                       array( 'id' => $contentTypeId, 'status' => $status ) );
        $this->backend->deleteByMatch( 'Content\\Type\\FieldDefinition',
                                           array( '_typeId' => $contentTypeId, '_status' => $status ) );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function createVersion( $userId, $contentTypeId, $fromVersion, $toVersion )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function copy( $userId, $contentTypeId, $status )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Unlink a content type group from a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @throws \ezp\Base\Exception\NotFound If group or type is not found
     * @throws \ezp\Base\Exception\BadRequest If type is not part of group or group is last on type (delete type instead)
     */
    public function unlink( $groupId, $contentTypeId, $status )
    {
        $list = $this->backend->find('Content\\Type', array( 'id' => $contentTypeId, 'status' => $status ) );
        if ( !isset( $list[0] ) )
            throw new NotFound( 'Content\\Type', "{$contentTypeId}' and status '{$status}" );

        $type = $list[0];
        if ( !in_array( $groupId, $type->groupIds ) )
            throw new BadRequest( "group:{$groupId}", "groupIds on Type:{$contentTypeId}" );

        if ( !isset( $type->groupIds[1] ) )
            throw new BadRequest( 'groups', "Type: {$contentTypeId}.\n"
                                          . "Can not remove last Group: {$groupId}, delete Type instead" );

        if ( !$this->backend->load('Content\\Type\\Group', $groupId ) )
            throw new NotFound( 'Content\\Type\\Group', $groupId );

        $this->backend->updateByMatch( 'Content\\Type',
                            array( 'id' => $contentTypeId, 'status' => $status ),
                            array( 'groupIds' => array_values( array_diff( $type->groupIds, array( $groupId ) ) ) ) );
    }

    /**
     * Link a content type group with a content type
     *
     * @param mixed $groupId
     * @param mixed $contentTypeId
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     * @throws \ezp\Base\Exception\NotFound If group or type is not found
     * @throws \ezp\Base\Exception\BadRequest If type is already part of group
     */
    public function link( $groupId, $contentTypeId, $status )
    {
        $list = $this->backend->find('Content\\Type', array( 'id' => $contentTypeId, 'status' => $status ) );
        if ( !isset( $list[0] ) )
            throw new NotFound( 'Content\\Type', "{$contentTypeId}' and status '{$status}" );

        $type = $list[0];
        if ( in_array( $groupId, $type->groupIds ) )
            throw new BadRequest( "group:{$groupId}", "groupIds on Type:{$contentTypeId}" );

        if ( !$this->backend->load('Content\\Type\\Group', $groupId ) )
            throw new NotFound( 'Content\\Type\\Group', $groupId );

        $this->backend->updateByMatch( 'Content\\Type',
                               array( 'id' => $contentTypeId, 'status' => $status ),
                               array( 'groupIds' => array_merge( $type->groupIds, array( $groupId ) ) ) );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function addFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function removeFieldDefinition( $contentTypeId, $status, $fieldDefinitionId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function updateFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function updateContentObjects( $contentTypeId, $status, $fieldDefinitionId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }
}
?>
