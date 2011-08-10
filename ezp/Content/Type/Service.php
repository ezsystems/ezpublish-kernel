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
    ezp\Base\Exception\PropertyNull,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Collection\LazyIdList,
    ezp\Base\Collection\Lazy,
    ezp\Base\Model,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\Type\Group,
    ezp\Persistence\Content\Type as TypeValue,
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
     * @throws RuntimeException If there are properties needed for create on $group w/o value
     */
    public function createGroup( Group $group )
    {
        $struct = new GroupCreateStruct();
        $this->fillStruct( $struct, $group );
        $obj = $this->handler->contentTypeHandler()->createGroup( $struct  );
        return $this->buildGroup( $group, $obj );
    }

    /**
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type\Group $group
     * @throws RuntimeException If there are properties needed for update on $group w/o value
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
     */
    public function deleteGroup( $groupId )
    {
        $this->handler->contentTypeHandler()->deleteGroup( $groupId  );
    }

    /**
     * Get an Content Type Group object by id
     *
     * @param int $groupId
     * @return \ezp\Content\Type\Group
     * @throws NotFound
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
     * Get an Content Type object by id
     *
     * @param int $contentTypeId
     * @param int $version
     * @return \ezp\Content\Type
     * @throws NotFound
     */
    public function load( $contentTypeId, $version = 0 )
    {
        $vo = $this->handler->contentTypeHandler()->load( $contentTypeId, $version );
        if ( !$vo )
            throw new NotFound( 'Content\\Type', $contentTypeId );
        return $this->buildType( new Type(), $vo );
    }

    /**
     * Get an Content Type object by group Id
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
     * Get an Content Type by identifier
     *
     * @param string $identifier
     * @return \ezp\Content\Type
     * @throws NotFound
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
     * @throws RuntimeException If property is missing on $do or has a value of null
     */
    protected function fillStruct( ValueObject $struct, Model $do )
    {
        foreach ( $struct as $prop => $value )
        {
           if ( !isset( $do->$prop ) )
               throw new PropertyNotFound( $prop, get_class( $do ) );
           $struct->$prop = $do->$prop;
        }
    }
}
