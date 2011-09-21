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
    ezp\User\Exception\FailedLogin,
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
     * Load a User object by login and password
     *
     * @param string $login
     * @param string $password
     * @return \ezp\User
     * @throws \ezp\Base\Exception\NotFound If user is not found
     * @throws \ezp\User\Exception\FailedLogin If password does not match
     */
    public function loadByCredentials( $login, $password )
    {
        $match = Configuration::getInstance( 'site' )->get( 'UserSettings', 'AuthenticateMatch', 'login' );
        $users = $this->handler->userHandler()->loadByLogin( $login, strpos( 'email', $match ) === false );

        if ( empty( $users ) )
            throw new NotFound( 'User', $login );

        foreach ( $users as $user )
        {
            if ( $user->passwordHash == self::createHash( $user->login, $password, $user->hashAlgorithm ) )
            {
                $content = $this->repository->getContentService()->load( $user->id );
                return $this->buildUser( $user, $content );
            }
        }
        throw new FailedLogin();
    }

    /**
     * Create user password hash
     *
     * @param string $login
     * @param string $password
     * @param int $type One of User::PASSWORD_HASH_* constants
     * @return string
     */
    protected static function createHash( $login, $password, $type )
    {
        if ( $type == User::PASSWORD_HASH_MD5_USER )
        {
            return md5( "$login\n$password" );
        }
        elseif ( $type == User::PASSWORD_HASH_MD5_SITE )
        {
            $site = Configuration::getInstance( 'site' )->get( 'UserSettings', 'SiteName', 'ez.no' );
            return md5( "$login\n$password\n$site" );
        }
        elseif ( $type == User::PASSWORD_HASH_PLAIN_TEXT )
        {
            return $password;
        }
        //elseif ( $type == User::PASSWORD_HASH_MD5_PASSWORD )
        return md5( $password );
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
     * @param \ezp\User $user
     */
    public function delete( User $user )
    {
        $this->handler->userHandler()->delete( $user->id );
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

        $parentLocation = $parentContent->getMainLocation();
        if ( !$parentLocation instanceof Location  )
            throw new Logic( 'createGroup', 'can not create group when $parentGroup is not created (persisted)' );

        $type = $this->repository->getContentTypeService()->load( $typeId );
        if ( !$type )
            throw new BadConfiguration( 'site.ini[UserSettings]UserGroupClassID', 'could not load type:' . $typeId );

        $content = new Content( $type, $this->repository->getUser() );
        $content->addParent( $parentLocation );
        $content->getState( 'properties' )->sectionId = $parentContent->sectionId;

        $fields = $content->getFields();
        if ( !isset( $fields['name'] ) )
            throw new PropertyNotFound( 'name', get_class( $content ) );
        else if ( !isset( $fields['description'] ) )
            throw new PropertyNotFound( 'description', get_class( $content ) );

        $fields['name'] = $name;
        $fields['description'] = $description;
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
     * @access private Used by $user->getGroups()
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
        foreach ( $contentUser->getLocations() as $location )
        {
            $parentContent = $location->getParent()->getContent();
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

        $newLocation = $content->addParent( $group->getState( 'content' )->getMainLocation() );
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

        if ( !count( $groups ) )// $groups is collection so need to use count()
            throw new Logic( 'unAssignGroup', 'can not remove $group, it seems to be last group on user' );

        $groupLocation = $group->getState( 'content' )->getMainLocation();
        if ( !$groupLocation instanceof Location  )
            throw new Logic( 'unAssignGroup', 'can not remove non created (persisted)$groupLocation on $group' );

        $locationKey = false;
        $locations = $content->getLocations();
        foreach ( $locations as $key => $location )
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

        unset( $locations[$locationKey] );
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
     * @access private Used by $group->getRoles()
     * @param mixed $id The user [group] id that returned roles are assigned to
     * @return \ezp\User\Role[]
     * @throws \ezp\Base\Exception\NotFound If role is not found
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
     * @param \ezp\User\Role $role
     */
    public function deleteRole( Role $role )
    {
        $this->handler->userHandler()->deleteRole( $role->id );
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
     * @access Private Used by $user->getPolicies()
     * @param mixed $id The user [group] id that returned roles are assigned to
     * @return \ezp\User\Policy[]
     * @throws \ezp\Base\Exception\NotFound If user is not found
     */
    public function loadPoliciesByUserId( $id )
    {
        $policies = $this->handler->userHandler()->loadPoliciesByUserId( $id );
        foreach ( $policies as &$value )
            $value = $this->buildPolicy( $value, new Proxy( $this, $value->roleId, 'loadRole' ) );
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
        if ( $content->getMainLocation()->parentId > 1 )
        {
            $parent = new Proxy( $this,
                                 $content->getMainLocation()->getParent()->getContent()->id,// not very lazy, callback / api?
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
