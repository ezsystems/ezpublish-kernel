<?php
/**
 * File containing the ezp\Content\Type\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Service as BaseService,
    ezp\Base\Exception\NotFound,
    ezp\Base\Collection\LazyIdList,
    ezp\Base\Collection\Lazy,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Model,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\Type\Group,
    ezp\Persistence\Content\Type as TypeValue,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\Group as GroupValue,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\ValueObject;

/**
 * Content Service, extends repository with content specific operations
 *
 */
class Service extends BaseService
{
    /**
     * Crate a Content Type Group object
     *
     * @param \ezp\Content\Type\Group $group
     * @return \ezp\Content\Type\Group
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     */
    public function createGroup( Group $group )
    {
        $struct = new GroupCreateStruct();
        $this->fillStruct( $struct, $group );
        $vo = $this->handler->contentTypeHandler()->createGroup( $struct  );
        return $this->buildGroup( $vo );
    }

    /**
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type\Group $group
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function updateGroup( Group $group )
    {
        $struct = new GroupUpdateStruct();
        $this->fillStruct( $struct, $group );
        $this->handler->contentTypeHandler()->updateGroup( $struct  );
    }

    /**
     * Update a Content Type Group object
     *
     * @param int $groupId
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function deleteGroup( $groupId )
    {
        $this->handler->contentTypeHandler()->deleteGroup( $groupId  );
    }

    /**
     * Get a Content Type Group object by id
     *
     * @param int $groupId
     * @return \ezp\Content\Type\Group
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function loadGroup( $groupId )
    {
        $vo = $this->handler->contentTypeHandler()->loadGroup( $groupId );
        if ( !$vo )
            throw new NotFound( 'Content\\Type\\Group', $groupId );
        return $this->buildGroup( $vo );
    }

    /**
     * Get all Content Type Groups
     *
     * @return \ezp\Content\Type\Group[]
     */
    public function loadAllGroups()
    {
        $list = $this->handler->contentTypeHandler()->loadAllGroups();
        foreach ( $list as $key => $vo )
            $list[$key] = $this->buildGroup( $vo );

        return $list;
    }

    /**
     * Create a Content Type object
     *
     * @param \ezp\Content\Type $contentType
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     */
    public function create( Type $contentType )
    {
        $struct = new CreateStruct();
        $this->fillStruct( $struct, $contentType, array( 'fieldDefinitions' ) );
        foreach ( $contentType->fields as $field )
        {
            $struct->fieldDefinitions[] = $field->getState( 'properties' );
        }
        $vo = $this->handler->contentTypeHandler()->create( $struct  );
        return $this->buildType( $vo );
    }

    /**
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type $contentType
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function update( Type $contentType )
    {
        $struct = new UpdateStruct();
        $this->fillStruct( $struct, $contentType );
        $this->handler->contentTypeHandler()->update( $contentType->id, $contentType->status, $struct  );
    }

    /**
     * Delete a Content Type object
     *
     * @param int $contentTypeId
     * @param int $status
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function delete( $contentTypeId, $status = 0 )
    {
        $this->handler->contentTypeHandler()->delete( $contentTypeId, $status );
    }

    /**
     * Get a Content Type object by id
     *
     * @param int $contentTypeId
     * @param int $status
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function load( $contentTypeId, $status = 0 )
    {
        $vo = $this->handler->contentTypeHandler()->load( $contentTypeId, $status );
        if ( !$vo )
            throw new NotFound( 'Content\\Type', $contentTypeId );
        return $this->buildType( $vo );
    }

    /**
     * Get Content Type objects by group Id
     *
     * @param int $groupId
     * @param int $status
     * @return \ezp\Content\Type[]
     */
    public function loadByGroupId( $groupId, $status = 0 )
    {
        $list = $this->handler->contentTypeHandler()->loadContentTypes( $groupId, $status );
        foreach ( $list as $key => $vo )
            $list[$key] = $this->buildType( $vo );

        return $list;
    }

    /**
     * Get a Content Type by identifier
     *
     * @param string $identifier
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function loadByIdentifier( $identifier )
    {
        throw new RuntimeException( "@TODO: Implement" );
    }

    /**
     * Link a content type to a group ( add a group to a type )
     *
     * @param Type $contentType
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     * @throws \ezp\Base\Exception\BadRequest If $groupId is not an group on type or is the last one
     */
    public function unlink( Type $contentType, Group $group )
    {
        $this->handler->contentTypeHandler()->unlink( $group->id, $contentType->id, $contentType->status );
    }

    /**
     * Link a content type to a group ( add a group to a type )
     *
     * @param Type $contentType
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     */
    public function link( Type $contentType, Group $group  )
    {
        $this->handler->contentTypeHandler()->link( $group->id, $contentType->id, $contentType->status );
    }

    /**
     * @param \ezp\Persistence\Content\Type $vo
     * @return \ezp\Content\Type
     */
    protected function buildType( TypeValue $vo )
    {
        $type = new Type();
        foreach ( $vo->fieldDefinitions as $fieldDefinitionVo )
        {
            $fieldDefinition = new FieldDefinition( $type, $fieldDefinitionVo->fieldType );
            $type->fields[] = $fieldDefinition->setState( array( 'properties' => $fieldDefinitionVo ) );
        }
        $type->setState( array( 'properties' => $vo,
                                'groups' => new LazyIdList( 'ezp\\Content\\Type\\Group',
                                                            $vo->groupIds,
                                                            $this,
                                                            'loadGroup' )
                         ) );
        return $type;
    }

    /**
     * @param \ezp\Persistence\Content\Type\Group $vo
     * @return \ezp\Content\Type\Group
     */
    protected function buildGroup( GroupValue $vo )
    {
        $group = new Group();
        $group->setState( array( 'properties' => $vo,
                                 'types' => new Lazy( 'ezp\\Content\\Type',
                                                      $this,
                                                      $vo->id,
                                                      'loadByGroupId' ) ) );
        return $group;
    }
}
