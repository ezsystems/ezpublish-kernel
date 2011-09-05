<?php
/**
 * File contains User Service
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Configuration,
    ezp\Base\Service as BaseService,
    ezp\Base\Proxy,
    ezp\Base\Collection\Lazy,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\NotFoundWithType,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\Logic,
    ezp\Content,
    ezp\Content\Location,
    ezp\User,
    ezp\User\Group,
    ezp\User\GroupAbleInterface,
    ezp\User\Role,
    ezp\User\Policy,
    ezp\Persistence\User as UserValueObject,
    ezp\Persistence\User\Role as RoleValueObject,
    ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Policy as PolicyValueObject;

/**
 * User Service, extends repository with user specific operations
 *
 */
class Service extends BaseService
{
    /**
     * Crate a User object
     *
     * @param \ezp\User $user
     * @return \ezp\User
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     * @throws \ezp\Base\Exception\NotFound If attached content object with same id does not exist
     */
    public function create( User $user )
    {
        $struct = new UserValueObject();
        $this->fillStruct( $struct, $user );
        $content = $this->repository->getContentService()->load( $user->id );// before create() to validate ->id
        $vo = $this->handler->userHandler()->create( $struct );
        return $this->buildUser( $vo, $content );
    }

    /**
     * Load a User object by id
     *
     * @param mixed $id
     * @return \ezp\User
     * @throws \ezp\Base\Exception\NotFound If user is not found
     */
    public function load( $id )
    {
        $content = $this->repository->getContentService()->load( $id );
        return $this->buildUser( $this->handler->userHandler()->load( $id ), $content );
    }

    /**
     * Update a User object
     *
     * @param \ezp\User $user
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     */
    public function update( User $user )
    {
        $struct = new UserValueObject();
        $this->fillStruct( $struct, $user );
        $this->handler->userHandler()->update( $struct );
    }

    /**
     * Delete a User object by id
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $this->handler->userHandler()->delete( $id );
    }

    /**
     * Crate a Group object
     *
     * Notice: Group related api currently deals with content in the background, see Readme.rst for info and constraints
     *
     * @param \ezp\User\Group $parentGroup
     * @param string $name
     * @param string $description
     * @return \ezp\User\Group
     * @throws \ezp\Base\Exception\PropertyNotFound If name or description properties (fields) are not found
     */
    public function createGroup( Group $parentGroup, $name, $description = '' )
    {
        $typeId = Configuration::getInstance( 'site' )->get( 'UserSettings', 'UserGroupClassID', 3 );
        $parentContent = $parentGroup->getState( 'content' );
        if ( !$parentContent instanceof Content  )
            throw new Logic( 'createGroup', 'can not create group when $parentGroup is not created (persisted)' );

        $parentLocation = $parentContent->mainLocation;
        if ( !$parentLocation instanceof Location  )
            throw new Logic( 'createGroup', 'can not create group when $parentGroup is not created (persisted)' );

        $type = $this->repository->getContentTypeService()->load( $typeId );
        if ( !$type )
            throw new BadConfiguration( 'site.ini[UserSettings]UserGroupClassID', 'could not load type:' . $typeId );

        $content = new Content( $type );
        $content->addParent( $parentLocation );
        $content->ownerId = $this->repository->getCurrentUser()->id;
        $content->getState( 'properties' )->sectionId = $parentContent->sectionId;

        if ( !isset( $content->fields['name'] ) )
            throw new PropertyNotFound( 'name', get_class( $content ) );
        else if ( !isset( $content->fields['description'] ) )
            throw new PropertyNotFound( 'description', get_class( $content ) );

        $content->fields['name'] = $name;
        $content->fields['description'] = $description;
        $content->name = array( 'eng-GB' => $name );// @todo remove when name handler is in place

        return $this->buildGroup( $this->repository->getContentService()->create( $content ) );
    }

    /**
     * Load a Group object by id
     *
     * Notice: Group related api currently deals with content in the background, see Readme.rst for info and constraints
     *
     * @param mixed $id
     * @return \ezp\User\Group
     * @throws \ezp\Base\Exception\NotFound If group is not found
     * @throws \ezp\Base\Exception\NotFoundWithType If content found with $id does not have correct typeId
     */
    public function loadGroup( $id )
    {
        $typeId = Configuration::getInstance( 'site' )->get( 'UserSettings', 'UserGroupClassID', 3 );
        $content = $this->repository->getContentService()->load( $id );
        if ( $content->typeId != $typeId )
            throw new NotFoundWithType( "User Group({$typeId})", $id );

        return $this->buildGroup( $content );
    }

    /**
     * Load a Group object by user id
     *
     * Notice: Group related api currently deals with content in the background, see Readme.rst for info and constraints
     *
     * @param mixed $id
     * @return \ezp\User\Group[]
     * @throws \ezp\Base\Exception\InvalidArgumentType If $id does not belong to a user object
     * @throws \ezp\Base\Exception\NotFoundWithType If any of locations user is assigned to is not a user group
     */
    public function loadGroupsByUserId( $id )
    {
        $configuration = Configuration::getInstance( 'site' );
        $userTypeId = $configuration->get( 'UserSettings', 'UserClassID', 4 );
        $groupTypeId = $configuration->get( 'UserSettings', 'UserGroupClassID', 3 );

        // @todo Implement more efficiently when there is api for getting parent locations (incl content) directly
        $contentUser = $this->repository->getContentService()->load( $id );
        if ( $contentUser->typeId != $userTypeId )
            throw new InvalidArgumentType( "id", 'id of user object, got typeId:' . $contentUser->typeId );

        $groups = array();
        foreach ( $contentUser->locations as $location )
        {
            $parentContent = $location->parent->content;
            if ( $parentContent->typeId != $groupTypeId )
                throw new NotFoundWithType( "User Group({$groupTypeId})", $parentContent->id );

            $groups[] = $this->buildGroup( $parentContent );
        }

        return $groups;
    }

    /**
     * Assign a Group to User
     *
     * Notice: Group related api currently deals with content in the background, see Readme.rst for info and constraints
     *
     * @param \ezp\User\Group $group
     * @param \ezp\User $user
     * @throws \ezp\Base\Exception\Logic If $object has not been persisted yet
     */
    public function assignGroup( Group $group, User $user )
    {
        $content = $user->getState( 'content' );
        if ( !$content instanceof Content  )
            throw new Logic( 'assignGroup', 'can not assign $parent location to non created (persisted) $object' );

        $groups = $user->getGroups();
        if ( $groups->indexOf( $group ) !== false )
            throw new Logic( 'assignGroup', 'can not assign group that is already assigned' );

        $newLocation = $content->addParent( $group->getState( 'content' )->mainLocation );
        $this->repository->getLocationService()->create( $newLocation );

        $groups[] = $group;
        $user->setState( array( 'groups' => $groups ) );
    }

    /**
     * Remove a Group assignment from a User
     *
     * Notice: Group related api currently deals with content in the background, see Readme.rst for info and constraints
     *
     * @todo: Either allow removing last group of a user or define which api should be used for deleting users + tree
     *
     * @param \ezp\User\Group $group
     * @param \ezp\User $user
     * @throws \ezp\Base\Exception\Logic If $object has not been persisted yet
     */
    public function unAssignGroup( Group $group, User $user )
    {
        $content = $user->getState( 'content' );
        if ( !$content instanceof Content  )
            throw new Logic( 'unAssignGroup', 'can not remove $group to non created (persisted) $user' );

        $groups = $user->getGroups();
        $key = $groups->indexOf( $group );
        if ( $key === false )
            throw new Logic( 'unAssignGroup', 'can not remove $group that is not part of $user->groups' );

        if ( !count( $groups ) )
            throw new Logic( 'unAssignGroup', 'can not remove $group, it seems to be last group on user' );

        $groupLocation = $group->getState( 'content' )->mainLocation;
        if ( !$groupLocation instanceof Location  )
            throw new Logic( 'unAssignGroup', 'can not remove non created (persisted)$groupLocation on $group' );

        $locationKey = false;
        foreach ( $content->locations as $key => $location )
        {
            if ( $location->parentId == $groupLocation->id )
            {
                $locationKey = $key;
                break;
            }
        }
        if ( $locationKey === false  )
            throw new Logic( 'unAssignGroup', 'could not find provided $groupLocation among locations on $user->content' );

        $this->repository->getLocationService()->delete( $location );

        unset( $content->locations[$locationKey] );
        unset( $content->getState( 'properties' )->locations[$locationKey] );//order should be same, so reusing key

        unset( $groups[$key] );
        $user->setState( array( 'groups' => $groups ) );
    }

    /**
     * Crate a Role object
     *
     * @param \ezp\User\Role $role
     * @return \ezp\User\Role
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     */
    public function createRole( Role $role )
    {
        $struct = new RoleValueObject();
        $this->fillStruct( $struct, $role, array( 'id' ) );
        $vo = $this->handler->userHandler()->createRole( $struct );
        return $this->buildRole( $vo );
    }

    /**
     * Load a Role object by id
     *
     * @param mixed $id
     * @return \ezp\User\Role
     * @throws \ezp\Base\Exception\NotFound If user is not found
     */
    public function loadRole( $id )
    {
        return $this->buildRole( $this->handler->userHandler()->loadRole( $id ) );
    }

    /**
     * Load list of Roles assigned to a certain user [group] by id
     *
     * @param mixed $id The user [group] id that returned roles are assigned to
     * @return \ezp\User\Role[]
     */
    public function loadRolesByGroupId( $id )
    {
        $roles = $this->handler->userHandler()->loadRolesByGroupId( $id );
        foreach ( $roles as &$value )
            $value = $this->buildRole( $value );
        return $roles;
    }

    /**
     * Update a Role object
     *
     * @param \ezp\User\Role $role
     * @throws \ezp\Base\Exception\PropertyNotFound If property is missing or has a value of null
     */
    public function updateRole( Role $role )
    {
        $struct = new RoleUpdateStruct();
        $this->fillStruct( $struct, $role );
        $this->handler->userHandler()->updateRole( $struct );
    }

    /**
     * Delete a Role object by id
     *
     * @param mixed $id
     */
    public function deleteRole( $id )
    {
        $this->handler->userHandler()->deleteRole( $id );
    }

    /**
     * Add a policy to a persisted role
     *
     * @param \ezp\User\Role $role
     * @param \ezp\User\Policy $policy
     */
    public function addPolicy( Role $role, Policy $policy )
    {
        $this->handler->userHandler()->addPolicy( $role->id, $policy->getState( 'properties' ) );
        $role->addPolicy( $policy );
    }

    /**
     * Remove a policy from a persisted role
     *
     * @param \ezp\User\Role $role
     * @param \ezp\User\Policy $policy
     */
    public function removePolicy( Role $role, Policy $policy )
    {
        $this->handler->userHandler()->removePolicy( $role->id, $policy->id );
        $role->removePolicy( $policy );
    }

    /**
     * Load list of Policies assigned and inherited on a user [group]
     *
     * @param mixed $id The user [group] id that returned roles are assigned to
     * @return \ezp\User\Policy[]
     */
    public function loadPoliciesByUserId( $id )
    {
        $policies = $this->handler->userHandler()->loadPoliciesByUserId( $id );
        foreach ( $policies as &$value )
            $value = $this->buildPolicy( $value, new Proxy( $this, $vo->roleId, 'loadRole' ) );
        return $policies;
    }

    /**
     * @param \ezp\User\Group $group
     * @param \ezp\User\Role $role
     * @throws \ezp\Base\Exception\InvalidArgumentValue If group is already contains role
     */
    public function assignRole( Group $group, Role $role )
    {
        $this->handler->userHandler()->assignRole( $group->id, $role->id );

        $roles = $group->getRoles();
        // Do not add if not loaded lazy loaded collection as it will duplicate object
        if ( !$roles instanceof Lazy || $roles->isLoaded() )
        {
            $roles[] = $role;
            $group->setState( array( 'roles' => $roles ) );
        }

        $role->getState( 'properties' )->groupIds[] = $group->id;
    }

    /**
     * @param \ezp\User\Group $group Can also take instance of user atm
     * @param \ezp\User\Role $role
     * @throws \ezp\Base\Exception\InvalidArgumentValue If group does not contain role
     */
    public function unAssignRole( GroupAbleInterface $group, Role $role )
    {
        $this->handler->userHandler()->unAssignRole( $group->id, $role->id );

        $roles = $group->getRoles();
        // Do not remove if not loaded lazy loaded collection as it will duplicate object
        if ( !$roles instanceof Lazy || $roles->isLoaded() )
        {
            unset( $roles[$roles->indexOf( $role )] );
            $group->setState( array( 'roles' => $roles ) );
        }

        $role->getState( 'properties' )->groupIds = array_values( array_diff( $role->groupIds, array( $group->id ) ) );
    }

    /**
     * @param \ezp\Persistence\User $vo
     * @param \ezp\Content $content
     * @return \ezp\User
     */
    protected function buildUser( UserValueObject $vo, Content $content )
    {
        $do = new User();
        $do->setState(
            array(
                "properties" => $vo,
                "content" => $content,
                "groups" => new Lazy(
                    "ezp\\User\\Group",
                    $this,
                    $vo->id,
                    "loadGroupsByUserId"
                ),
                "roles" => new Lazy(
                    "ezp\\User\\Role",
                    $this,
                    $vo->id,
                    "loadRolesByGroupId"
                ),
                "policies" => new Lazy(
                    "ezp\\User\\Policy",
                    $this,
                    $vo->id,
                    "loadPoliciesByUserId"
                ),
            )
        );
        return $do;
    }

    /**
     * @param \ezp\Content $content
     * @return \ezp\User\Group
     */
    protected function buildGroup( Content $content )
    {
        $parent = null;
        if ( $content->mainLocation->parentId > 1 )
        {
            $parent = new Proxy( $this,
                                 $content->mainLocation->parent->content->id,// not very lazy, callback / api?
                                 'loadGroup' );
        }
        $do = new Group( $content );
        $do->setState(
            array(
                "parent" => $parent,
                "roles" => new Lazy(
                    "ezp\\User\\Role",
                    $this,
                    $content->id,
                    "loadRolesByGroupId"
                ),
            )
        );
        return $do;
    }

    /**
     * @param \ezp\Persistence\User\Role $vo
     * @return \ezp\User\Role
     */
    protected function buildRole( RoleValueObject $vo )
    {
        $do = new Role();
        $policies = array();
        foreach ( $vo->policies as $policyVo )
        {
            $policies[] = $this->buildPolicy( $policyVo, $do );
        }
        $do->setState(
            array(
                "properties" => $vo,
                "policies" => new TypeCollection( 'ezp\\User\\Policy', $policies ),
            )
        );
        return $do;
    }

    /**
     * @param \ezp\Persistence\User\Policy $vo
     * @param \ezp\User\Role|\ezp\Base\Proxy $role
     * @return \ezp\User\Policy
     */
    protected function buildPolicy( PolicyValueObject $vo, $role )
    {
        $do = new Policy( $role );
        $do->setState(
            array(
                "properties" => $vo,
            )
        );
        return $do;
    }
}
