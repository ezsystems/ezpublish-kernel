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
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\PropertyNotFound,
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
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\ValueObject;

/**
 * Content Service, extends repository with content specific operations
 *
 * @todo Figure out which methods should manipulate object provided or add doc on having to re fetch object.
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
        $vo = $this->handler->contentTypeHandler()->createGroup( $struct );
        return $this->buildGroup( $vo );
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
        $this->handler->contentTypeHandler()->updateGroup( $struct );
    }

    /**
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type\Group $group
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function deleteGroup( Group $group )
    {
        $this->handler->contentTypeHandler()->deleteGroup( $group->id );
    }

    /**
     * Create a Content Type object
     *
     * @param \ezp\Content\Type $type
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a empty value
     * @throws \ezp\Base\Exception\Logic If a group is _not_ persisted, or if type / fields is
     */
    public function create( Type $type )
    {
        if ( $type->id )
            throw new Logic( "Type\\Service->create()", '$type seems to already be persisted' );

        $struct = new CreateStruct();
        $this->fillStruct( $struct, $type, array( 'fieldDefinitions', 'groupIds' ) );
        foreach ( $type->fields as $fieldDefinition )
        {
            if ( $fieldDefinition->id )
                throw new Logic( "Type\\Service->create()", '->fields can not already be persisted' );

            $fieldDefStruct = $fieldDefinition->getState( 'properties' );
            $fieldDefStruct->defaultValue = $fieldDefinition->type->setFieldValue( new FieldValue );
            $struct->fieldDefinitions[] = $fieldDefStruct;
        }

        if ( !isset( $type->groups[0] ) )
            throw new PropertyNotFound( 'groups', get_class( $type ) );

        // @todo Remove this if api is introduced on Type to add / remove fields / groups (but still verify values)
        foreach ( $type->groups as $group )
        {
            if ( !$group->id )
                throw new Logic( "Type\\Service->create()", '->groups needs to be persisted before adding it to type' );

            $struct->groupIds[] = $group->id;
        }
        $vo = $this->handler->contentTypeHandler()->create( $struct );
        return $this->buildType( $vo );
    }

    /**
     * Create a Content Type object and publish in one operation
     *
     * @param \ezp\Content\Type $type
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a empty value
     * @throws \ezp\Base\Exception\Logic If a group is _not_ persisted, or if type / fields is
     */
    public function createAndPublish( Type $type )
    {
        $type->getState( 'properties' )->status = TypeValue::STATUS_DEFINED;
        return $this->create( $type );
    }

    /**
     * Get a Content Type object by id
     *
     * @param int $typeId
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If type can not be found
     */
    public function load( $typeId )
    {
        return $this->buildType( $this->handler->contentTypeHandler()->load( $typeId, TypeValue::STATUS_DEFINED ) );
    }

    /**
     * Get a Content Type object draft by id
     *
     * @param int $typeId
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If type draft can not be found
     */
    public function loadDraft( $typeId )
    {
        return $this->buildType( $this->handler->contentTypeHandler()->load( $typeId, TypeValue::STATUS_DRAFT ) );
    }

    /**
     * Get Content Type objects by group Id
     *
     * @param int $groupId
     * @return \ezp\Content\Type[]
     */
    public function loadByGroupId( $groupId )
    {
        $list = $this->handler->contentTypeHandler()->loadContentTypes( $groupId, TypeValue::STATUS_DEFINED );
        foreach ( $list as $key => $vo )
            $list[$key] = $this->buildType( $vo );

        return $list;
    }

    /**
     * Get Content Type draft objects by group Id
     *
     * @param int $groupId
     * @return \ezp\Content\Type[]
     */
    public function loadDraftsByGroupId( $groupId )
    {
        $list = $this->handler->contentTypeHandler()->loadContentTypes( $groupId, TypeValue::STATUS_DRAFT );
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
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type $type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function update( Type $type )
    {
        $struct = new UpdateStruct();
        $this->fillStruct( $struct, $type );
        $this->handler->contentTypeHandler()->update( $type->id, $type->status, $struct );
    }

    /**
     * Delete a Content Type object
     *
     * @param \ezp\Content\Type $type
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     */
    public function delete( Type $type )
    {
        $this->handler->contentTypeHandler()->delete( $type->id, $type->status );
    }

    /**
     * Copy Type incl fields and groupIds to a new Type object
     *
     * New Type will have $userId as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @param mixed $userId
     * @param mixed $typeId
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\NotFound If user or published type is not found
     */
    public function copy( $userId, $typeId )
    {
        return $this->buildType( $this->handler->contentTypeHandler()->copy( $userId, $typeId, TypeValue::STATUS_DEFINED ) );
    }

    /**
     * Un-Link a content type from a group ( remove a group from a type )
     *
     * @param \ezp\Content\Type $type
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     * @throws \ezp\Base\Exception\BadRequest If $groupId is not an group on type or is the last one
     */
    public function unlink( Type $type, Group $group )
    {
        $this->handler->contentTypeHandler()->unlink( $group->id, $type->id, $type->status );
    }

    /**
     * Link a content type to a group ( add a group to a type )
     *
     * @param \ezp\Content\Type $type
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     */
    public function link( Type $type, Group $group  )
    {
        $this->handler->contentTypeHandler()->link( $group->id, $type->id, $type->status );
    }

    /**
     * Adds a new field definition to an existing Type.
     *
     * @param \ezp\Content\Type $type
     * @param FieldDefinition $field
     * @throws \ezp\Base\Exception\InvalidArgumentType If field has id already
     * @throws \ezp\Base\Exception\NotFound If type is not found
     */
    public function addFieldDefinition( Type $type, FieldDefinition $field  )
    {
        if ( $field->id )
            throw new InvalidArgumentType( '$field->id', 'false' );

        $fieldDefStruct = $field->getState( 'properties' );
        $fieldDefStruct->defaultValue = $field->type->setFieldValue( new FieldValue );
        $this->handler->contentTypeHandler()->addFieldDefinition(
            $type->id,
            $type->status,
            $fieldDefStruct
        );
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @param \ezp\Content\Type $type
     * @param FieldDefinition $field
     * @throws \ezp\Base\Exception\NotFound If field/type is not found
     */
    public function removeFieldDefinition( Type $type, FieldDefinition $field  )
    {
        $this->handler->contentTypeHandler()->removeFieldDefinition(
            $type->id,
            $type->status,
            $field->id
        );
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @param \ezp\Content\Type $type
     * @param FieldDefinition $field
     * @throws \ezp\Base\Exception\NotFound If field/type is not found
     */
    public function updateFieldDefinition( Type $type, FieldDefinition $field  )
    {
        $fieldDefStruct = $field->getState( 'properties' );
        $fieldDefStruct->defaultValue = $field->type->setFieldValue( new FieldValue );
        $this->handler->contentTypeHandler()->updateFieldDefinition(
            $type->id,
            $type->status,
            $fieldDefStruct
        );
    }

    /**
     * Publish Type and update content objects.
     *
     * Updates content objects, depending on the changed field definitions.
     *
     * @param \ezp\Content\Type $type The type draft to publish
     * @throws \ezp\Base\Exception\NotFound If type draft is not found
     */
    public function publish( Type $type  )
    {
        $this->handler->contentTypeHandler()->publish( $type->id );
    }

    /**
     * @param \ezp\Persistence\Content\Type $vo
     * @return \ezp\Content\Type
     */
    protected function buildType( TypeValue $vo )
    {
        $type = new Type();
        $fields = $type->getFields();
        foreach ( $vo->fieldDefinitions as $fieldDefinitionVo )
        {
            $fieldDefinition = new FieldDefinition( $type, $fieldDefinitionVo->fieldType );
            $fields[] = $fieldDefinition->setState( array( 'properties' => $fieldDefinitionVo ) );
        }
        $type->setState(
            array(
                "properties" => $vo,
                "groups" => new LazyIdList(
                    "ezp\\Content\\Type\\Group",
                    $vo->groupIds,
                    $this,
                    "loadGroup"
                )
            )
        );
        return $type;
    }

    /**
     * @param \ezp\Persistence\Content\Type\Group $vo
     * @return \ezp\Content\Type\Group
     */
    protected function buildGroup( GroupValue $vo )
    {
        $group = new Group();
        $group->setState(
            array(
                "properties" => $vo,
                "types" => new Lazy(
                    "ezp\\Content\\Type",
                    $this,
                    $vo->id,
                    "loadByGroupId"
                )
            )
        );
        return $group;
    }
}
