<?php
/**
 * File containing the ezp\Content\Type\Service class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Service as BaseService,
    ezp\Base\Exception\Forbidden,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\Logic,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Collection\LazyType,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Collection\ReadOnly as ReadOnlyCollection,
    ezp\Base\Model,
    ezp\Content\Type,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Content\Type\Group\Concrete as ConcreteGroup,
    ezp\Content\Type\Group\Proxy as ProxyGroup,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\FieldType\Value as FieldTypeValue,
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
     * @todo Validate that identifier is not already in use
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to create provided object
     */
    public function createGroup( Group $group )
    {
        if ( !$this->repository->canUser( 'create', $group ) )
            throw new Forbidden( 'Type\\Group', 'create' );

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
     * @todo Validate that identifier is not already in use (if it has been changed?)
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function updateGroup( Group $group )
    {
        if ( !$this->repository->canUser( 'edit', $group ) )
            throw new Forbidden( 'Type\\Group', 'edit' );

        $struct = new GroupUpdateStruct();
        $this->fillStruct( $struct, $group );
        $this->handler->contentTypeHandler()->updateGroup( $struct );
    }

    /**
     * Update a Content Type Group object
     *
     * @param \ezp\Content\Type\Group $group
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to delete provided object
     */
    public function deleteGroup( Group $group )
    {
        if ( !$this->repository->canUser( 'delete', $group ) )
            throw new Forbidden( 'Type\\Group', 'delete' );

        $this->handler->contentTypeHandler()->deleteGroup( $group->id );
    }

    /**
     * Create a Content Type object
     *
     * @param \ezp\Content\Type $type
     * @param \ezp\Content\Type\Group[] $linkGroups Required array of Type\Group objects to link type with (must contain one)
     * @param \ezp\Content\Type\FieldDefinition[] $addFields Optional array of fields to add on new Type
     * @return \ezp\Content\Type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a empty value
     * @throws \ezp\Base\Exception\Logic If a group is _not_ persisted, or if type / fields is
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $type->identifier is in use
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $field->identifier is used in several fields
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to create provided object
     */
    public function create( Type $type, array $linkGroups, array $addFields = array() )
    {
        if ( $type->id )
            throw new Logic( "Type\\Service->create()", '$type seems to already be persisted' );

        if ( !$this->repository->canUser( 'create', $type ) )
            throw new Forbidden( 'Type', 'create' );

        $identifiers = array();
        $struct = new CreateStruct();
        $this->fillStruct(
            $struct, $type,
            array( 'fieldDefinitions', 'groupIds', 'urlAliasSchema' )
        );
        foreach ( $addFields as $fieldDefinition )
        {
            if ( !$fieldDefinition instanceof FieldDefinition )
                throw new Logic( "Type\\Service->create()", '$addFields needs to be instance of Type\\FieldDefinition' );

            if ( $fieldDefinition->id )
                throw new Logic( "Type\\Service->create()", '$addFields can not already be persisted' );

            if ( in_array( $fieldDefinition->identifier, $identifiers ) )
                throw new InvalidArgumentValue( '$field->identifier', "{$fieldDefinition->identifier} (already exists)" );

            $fieldDefStruct = $fieldDefinition->getState( 'properties' );
            $fieldDefStruct->defaultValue = $fieldDefinition->type->toFieldValue();
            $struct->fieldDefinitions[] = $fieldDefStruct;
            $identifiers[] = $fieldDefStruct->identifier;
        }

        if ( !isset( $linkGroups[0] ) )
            throw new PropertyNotFound( 'groups', get_class( $type ) );

        if ( $this->loadIdIfExistsByIdentifier( $type->identifier ) )
            throw new InvalidArgumentValue( '$type->identifier', "{$type->identifier} (already exists)" );

        // @todo Remove this if api is introduced on Type to add / remove fields / groups (but still verify values)
        foreach ( $linkGroups as $group )
        {
            if ( !$group instanceof Group )
                throw new Logic( "Type\\Service->create()", '$linkGroups needs to be instance of Type\\Group' );
            if ( !$group->id )
                throw new Logic( "Type\\Service->create()", '$linkGroups needs to be persisted before adding it to type' );

            $struct->groupIds[] = $group->id;
        }
        $vo = $this->handler->contentTypeHandler()->create( $struct );
        return $this->buildType( $vo );
    }

    /**
     * Create a Content Type object and publish in one operation
     *
     * @param \ezp\Content\Type $type
     * @param \ezp\Content\Type\Group[] $linkGroups Required array of Type\Group objects to link type with (must contain one)
     * @param \ezp\Content\Type\Field[] $addFields Optional array of fields to add on new Type
     * @return \ezp\Content\Type
     * @uses create()
     */
    public function createAndPublish( Type $type, array $linkGroups, array $addFields = array() )
    {
        $type->getState( 'properties' )->status = TypeValue::STATUS_DEFINED;
        return $this->create( $type, $linkGroups, $addFields );
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
        return $this->buildType( $this->handler->contentTypeHandler()->loadByIdentifier( $identifier ) );
    }

    /**
     * Check if content type exists by identifier, return id if so.
     *
     * @param string $identifier
     * @return int|false
     */
    protected function loadIdIfExistsByIdentifier( $identifier )
    {
        try
        {
            $vo = $this->handler->contentTypeHandler()->loadByIdentifier( $identifier );
        }
        catch ( NotFound $e )
        {
            return false;
        }

        if ( !$vo instanceof TypeValue )
            throw new Logic( "typeHandler->loadByIdentifier() did not return ezp\\Persistence\\Content\\Type" );

        return $vo->id;
    }

    /**
     * Update a Content Type object
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @param \ezp\Content\Type $type
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $type->identifier is used on another type object
     * @todo Consider adding fieldDefinitions on update struct when we have dirty state and knowledge about which
     * one has been updated (not added / removed, there are separate api's for that). But remember to validate
     * fieldDefinition->identifier when this is added.
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function update( Type $type )
    {
        if ( !$this->repository->canUser( 'edit', $type ) )
            throw new Forbidden( 'Type', 'edit' );

        $id = $this->loadIdIfExistsByIdentifier( $type->identifier );
        if ( $id !== false && $id != $type->id )
            throw new InvalidArgumentValue( '$type->identifier', "{$type->identifier} (already exists)" );

        $struct = new UpdateStruct();
        $this->fillStruct( $struct, $type );
        $this->handler->contentTypeHandler()->update( $type->id, $type->status, $struct );
    }

    /**
     * Delete a Content Type object
     *
     * @param \ezp\Content\Type $type
     * @throws \ezp\Base\Exception\NotFound If object can not be found
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to delete provided object
     */
    public function delete( Type $type )
    {
        if ( !$this->repository->canUser( 'delete', $type ) )
            throw new Forbidden( 'Type', 'delete' );

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
     * @todo Change to take objects in input? ( more consistent with rest and removes needs for lots of NotFound possibilities )
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to create provided object
     */
    public function copy( $userId, $typeId )
    {
        if ( $this->repository->getUser()->hasAccessTo( 'class', 'create' ) !== true )
            throw new Forbidden( 'Type', 'create' );

        return $this->buildType( $this->handler->contentTypeHandler()->copy( $userId, $typeId, TypeValue::STATUS_DEFINED ) );
    }

    /**
     * Un-Link a content type from a group ( remove a group from a type )
     *
     * @param \ezp\Content\Type $type
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $group is not on type or type is not on $group
     * @throws \ezp\Base\Exception\InvalidArgumentType If $group or $type is missing id value
     * @throws \ezp\Base\Exception\BadRequest If $group is the last group on type
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to (un)link provided object
     */
    public function unlink( Type $type, Group $group )
    {
        if ( !$type->id )
           throw new InvalidArgumentType( '$type->id', 'int' );

        if ( !$group->id )
            throw new InvalidArgumentType( '$group->id', 'int' );

        if ( !$this->repository->canUser( 'link', $type, $group ) )
            throw new Forbidden( 'Type', 'unlink' );

        $index = $type->getGroups()->indexOf( $group );
        if ( $index === false )
            throw new InvalidArgumentValue( '$group', 'Not part of $type->groups' );

        $index2 = $group->getTypes()->indexOf( $type );
        if ( $index2 === false )
            throw new InvalidArgumentValue( '$type', 'Not part of $group->types' );

        $this->handler->contentTypeHandler()->unlink( $group->id, $type->id, $type->status );

        $groups = $type->getGroups()->getArrayCopy();
        unset( $groups[$index] );
        $type->setState( array( 'groups' => new ReadOnlyCollection( $groups ) ) );

        $types = $group->getTypes()->getArrayCopy();
        unset( $types[$index2] );
        $group->setState( array( 'types' => new ReadOnlyCollection( $types ) ) );
    }

    /**
     * Link a content type to a group ( add a group to a type )
     *
     * @param \ezp\Content\Type $type
     * @param Group $group
     * @throws \ezp\Base\Exception\NotFound If type or group is not found
     * @throws \ezp\Base\Exception\InvalidArgumentType If $group does not have id value
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to link provided object
     */
    public function link( Type $type, Group $group  )
    {
        if ( !$group->id )
            throw new InvalidArgumentType( '$group->id', 'int' );

        if ( !$this->repository->canUser( 'link', $type, $group ) )
            throw new Forbidden( 'Type', 'link' );

        $this->handler->contentTypeHandler()->link( $group->id, $type->id, $type->status );

        $groups = $type->getGroups()->getArrayCopy();
        $groups[] = $group;
        $type->setState( array( 'groups' => new ReadOnlyCollection( $groups ) ) );

        $types = $group->getTypes()->getArrayCopy();
        $types[] = $type;
        $group->setState( array( 'types' => new ReadOnlyCollection( $types ) ) );
    }

    /**
     * Adds a new field definition to an existing Type.
     *
     * @param \ezp\Content\Type $type
     * @param \ezp\Content\Type\FieldDefinition $field
     * @throws \ezp\Base\Exception\InvalidArgumentType If field has id already
     * @throws \ezp\Base\Exception\NotFound If type is not found
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $field->identifier is used in existing field on $type
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function addFieldDefinition( Type $type, FieldDefinition $field  )
    {
        if ( $field->id )
            throw new InvalidArgumentType( '$field->id', 'false' );

        if ( !$this->repository->canUser( 'edit', $type ) )
            throw new Forbidden( 'Type', 'edit' );

        foreach ( $type->fields as $existingField )
        {
            if ( $existingField->identifier == $field->identifier )
                throw new InvalidArgumentValue( '$field->identifier', "{$field->identifier} (already exists)" );
        }

        $fieldDefStruct = $field->getState( 'properties' );
        $fieldDefStruct->defaultValue = $field->type->toFieldValue();
        $fieldDefStruct = $this->handler->contentTypeHandler()->addFieldDefinition(
            $type->id,
            $type->status,
            $fieldDefStruct
        );
        $field->setState( array( 'properties' => $fieldDefStruct ) );

        // @todo deal with ordering
        $fields = $type->getFields()->getArrayCopy();
        $fields[] = $field;
        $type->setState( array( 'fields' => new ReadOnlyCollection( $fields ) ) );
        $type->getState( 'properties' )->fieldDefinitions[] = $fieldDefStruct;
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @param \ezp\Content\Type $type
     * @param \ezp\Content\Type\FieldDefinition $field
     * @throws \ezp\Base\Exception\InvalidArgumentType If $field->id is false
     * @throws \ezp\Base\Exception\NotFound If field/type is not found
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $field is not an group on type
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function removeFieldDefinition( Type $type, FieldDefinition $field  )
    {
        if ( !$field->id )
            throw new InvalidArgumentType( '$field->id', 'int' );

        $index = $type->getFields()->indexOf( $field );
        if ( $index === false )
            throw new InvalidArgumentValue( '$field', 'Not part of $type->fields' );

        if ( !$this->repository->canUser( 'edit', $type ) )
            throw new Forbidden( 'Type', 'edit' );

        $this->handler->contentTypeHandler()->removeFieldDefinition(
            $type->id,
            $type->status,
            $field->id
        );
        $fields = $type->getFields()->getArrayCopy();
        unset( $fields[$index] );
        $type->setState( array( 'fields' => new ReadOnlyCollection( $fields ) ) );
        unset( $type->getState( 'properties' )->fieldDefinitions[$index] );// should be in same order so reuses index
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @param \ezp\Content\Type $type
     * @param \ezp\Content\Type\FieldDefinition $field
     * @throws \ezp\Base\Exception\InvalidArgumentType If $field->id is false
     * @throws \ezp\Base\Exception\NotFound If field/type is not found
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $field->identifier is used in existing field on $type
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function updateFieldDefinition( Type $type, FieldDefinition $field  )
    {
        if ( !$field->id )
            throw new InvalidArgumentType( '$field->id', 'int' );

        if ( !$this->repository->canUser( 'edit', $type ) )
            throw new Forbidden( 'Type', 'edit' );

        foreach ( $type->fields as $existingField )
        {
            if ( $existingField->id != $field->id && $existingField->identifier == $field->identifier )
                throw new InvalidArgumentValue( '$field->identifier', "{$field->identifier} (already exists on another field)" );
        }

        $fieldDefStruct = $field->getState( 'properties' );
        $fieldDefStruct->defaultValue = $field->type->toFieldValue();
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
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     */
    public function publish( Type $type  )
    {
        if ( !$this->repository->canUser( 'edit', $type ) )
            throw new Forbidden( 'Type', 'edit' );

        $this->handler->contentTypeHandler()->publish( $type->id );
    }

    /**
     * Creates a new draft for the published content type $type
     * @param \ezp\Content\Type $type The type to create a draft for
     * @throws \ezp\Base\Exception\Forbidden If user does not have access to edit provided object
     * @throws \ezp\Base\Exception\Logic If $type is not persisted
     * @throws \ezp\Base\Exception\Logic If $type doesn't have the DEFINED status
     * @throws \ezp\Base\Exception\Forbidden If draft already exists and is owned by another user
     * @return \ezp\Content\Type A new draft for the provided type or existing one for current user if it exist
     */
    public function createDraft( Type $type )
    {
        if ( !$this->repository->canUser( 'edit', $type ) )
            throw new Forbidden( 'Type', 'edit' );

        if ( !$type->id )
            throw new Logic( "Type\\Service->create()", '$type doesn\'t seem to be persisted' );

        if ( $type->status !== TypeValue::STATUS_DEFINED )
            throw new Logic( 'Type\\Service->create()', '$type doesn\'t have the DEFINED status' );

        try
        {
            $draft = $this->loadDraft( $type->id );
            if ( $draft->modifierId != $this->repository->getUser()->id )
                throw new Forbidden( 'Type', 'edit existing draft' );

            return $draft;
        }
        catch ( NotFound $e )
        {
            // Do nothing
        }

        return $this->buildType(
            $this->handler->contentTypeHandler()->createDraft( $this->repository->getUser()->id, $type->id )
        );
    }

    /**
     * @param \ezp\Persistence\Content\Type $vo
     * @return \ezp\Content\Type
     */
    protected function buildType( TypeValue $vo )
    {
        $type = new ConcreteType();
        $fields = array();
        foreach ( $vo->fieldDefinitions as $fieldDefinitionVo )
        {
            $fieldDefinition = new FieldDefinition( $type, $fieldDefinitionVo->fieldType );
            $fieldDefinition->setState( array( 'properties' => $fieldDefinitionVo ) );
            if ( isset( $fieldDefinitionVo->defaultValue->data ) )
                $fieldDefinition->setDefaultValue( $fieldDefinitionVo->defaultValue->data );
            $fields[] = $fieldDefinition;
        }
        $groups = array();
        foreach ( $vo->groupIds as $groupId )
        {
            $groups[] = new ProxyGroup( $groupId, $this );
        }
        $type->setState(
            array(
                "properties" => $vo,
                "fields" => new ReadOnlyCollection( $fields ),
                "groups" => new ReadOnlyCollection( $groups ),
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
        $group = new ConcreteGroup();
        $group->setState(
            array(
                "properties" => $vo,
                "types" => new LazyType(
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
