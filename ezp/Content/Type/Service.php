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
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\Type\Group,
    ezp\Persistence\Content\Type as TypeValue,
    ezp\Persistence\Content\Type\Group as GroupValue,
    RuntimeException;

/**
 * Content Service, extends repository with content specific operations
 *
 */
class Service extends BaseService
{
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
        $contentType = $this->handler->contentTypeHandler()->load( $contentTypeId, $version );
        if ( !$contentType )
            throw new NotFound( 'Content\\Type', $contentTypeId );
        return $this->buildType( $contentType );
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
            $list[$key] = $this->buildType( $vo );

        return $list;
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
        $obj = $this->handler->contentTypeHandler()->loadGroup( $groupId );
        if ( !$obj )
            throw new NotFound( 'Content\\Type\\Group', $groupId );
        return $this->buildGroup( $obj );
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
                                'typeGroups' => new LazyIdList( 'ezp\\Content\\Type\\Group',
                                                                $vo->contentTypeGroupIds,
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
        $obj = new Group();
        $obj->setState( array( 'properties' => $vo,
                               'contentTypes' => new Lazy( 'ezp\\Content\\Type',
                                                           $this,
                                                           $vo->id,
                                                           'loadByGroupId' ) ) );
        return $obj;
    }
}
