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
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Collection\LazyIdList,
    ezp\Base\Collection\Lazy,
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
        return $this->buildGroup( $group, $vo );
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
        return $this->buildGroup( new Group(), $vo );
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
            $list[$key] = $this->buildGroup( new Group(), $vo );

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
        $this->fillStruct( $struct, $contentType );
        $vo = $this->handler->contentTypeHandler()->create( $struct  );
        return $this->buildType( $contentType, $vo );
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
        $this->handler->contentTypeHandler()->update( $contentType->id, $contentType->version, $struct  );
    }

    /**
     * Delete a Content Type object
     *
     * @param int $contentTypeId
     * @param int $version
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function delete( $contentTypeId, $version = 0 )
    {
        $this->handler->contentTypeHandler()->delete( $contentTypeId, $version );
    }

    /**
     * Get a Content Type object by id
     *
     * @param int $contentTypeId
     * @param int $version
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function load( $contentTypeId, $version = 0 )
    {
        $vo = $this->handler->contentTypeHandler()->load( $contentTypeId, $version );
        if ( !$vo )
            throw new NotFound( 'Content\\Type', $contentTypeId );
        return $this->buildType( new Type(), $vo );
    }

    /**
     * Get Content Type objects by group Id
     *
     * @param int $groupId
     * @param int $version
     * @return \ezp\Content\Type[]
     */
    public function loadByGroupId( $groupId, $version = 0 )
    {
        $list = $this->handler->contentTypeHandler()->loadContentTypes( $groupId, $version );
        foreach ( $list as $key => $vo )
            $list[$key] = $this->buildType( new Type(), $vo );

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
     * @param \ezp\Content\Type $type
     * @param \ezp\Persistence\Content\Type $vo
     * @return \ezp\Content\Type
     */
    protected function buildType( Type $type, TypeValue $vo )
    {
        foreach ( $vo->fieldDefinitions as $fieldDefinitionVo )
        {
            $fieldDefinition = new FieldDefinition( $type, $fieldDefinitionVo->fieldType );
            $type->fields[] = $fieldDefinition->setState( array( 'properties' => $fieldDefinitionVo ) );
        }
        $type->setState( array( 'properties' => $vo,
                                'groups' => new LazyIdList( 'ezp\\Content\\Type\\Group',
                                                            $vo->contentTypeGroupIds,
                                                            $this,
                                                            'loadGroup' )
                         ) );
        return $type;
    }

    /**
     * @param \ezp\Content\Type\Group $group
     * @param \ezp\Persistence\Content\Type\Group $vo
     * @return \ezp\Content\Type\Group
     */
    protected function buildGroup( Group $group, GroupValue $vo )
    {
        $group->setState( array( 'properties' => $vo,
                                 'types' => new Lazy( 'ezp\\Content\\Type',
                                                      $this,
                                                      $vo->id,
                                                      'loadByGroupId' ) ) );
        return $group;
    }

    /**
     * Fill in property values in struct from model
     *
     * @param \ezp\Persistence\ValueObject $struct
     * @param \ezp\Base\Model $do
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing on $do or has a value of null
     *                                              Unless one of these properties which is filled by conventions:
     *                                              - remoteId
     *                                              - created
     *                                              - modified
     *                                              - creatorId
     *                                              - modifierId
     */
    protected function fillStruct( ValueObject $struct, Model $do )
    {
        $state = $do->getState();
        $vo = $state['properties'];
        foreach ( $struct as $property => $value )
        {
            // set property value if there is one
            if ( isset( $vo->$property ) )
            {
                $struct->$property = $vo->$property;
                continue;
            }

            // set by convention if match, if not throw PropertyNotFound exception
            switch ( $property )
            {
                case 'remoteId':
                    $struct->$property = md5( uniqid( get_class( $do ), true ) );
                    break;
                case 'created':
                case 'modified':
                    $struct->$property = time();
                    break;
                case 'creatorId':
                case 'modifierId':
                    $struct->$property = 14;// @todo Use user object when that is made part of repository/services
                    break;
                default:
                    throw new PropertyNotFound( $property, get_class( $do ) );
            }
        }
    }
}
