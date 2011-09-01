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
    ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\NotFoundWithType,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\Logic,
    ezp\Content,
    ezp\User,
    ezp\User\Group,
    ezp\User\GroupLocation,
    ezp\User\LocatableInterface,
    ezp\User\Location as AbstractUserLocation,
    ezp\User\UserLocation,
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
     * @todo What about content object?
     */
    public function delete( $id )
    {
        $this->handler->userHandler()->delete( $id );
    }

    /**
     * Crate a Group object
     *
     * @param \ezp\User\GroupLocation $parentGroupLocation
     * @param string $name
     * @param string $description
     * @return \ezp\User\Group
     * @throws \ezp\Base\Exception\PropertyNotFound If name or description properties (fields) are not found
     */
    public function createGroup( GroupLocation $parentGroupLocation, $name, $description = '' )
    {
        $typeId = Configuration::getInstance( 'site' )->get( 'UserSettings', 'UserGroupClassID', 3 );
        $parentLocation = $parentGroupLocation->getState( 'location' );

        $type = $this->repository->getContentTypeService()->load( $typeId );
        if ( !$type )
            throw new BadConfiguration( 'site.ini[UserSettings]UserGroupClassID', 'could not load type:' . $typeId );

        $content = new Content( $type );
        $content->addParent( $parentLocation );
        $content->ownerId = $this->repository->getCurrentUser()->id;
        $content->getState('properties')->sectionId = $parentLocation->content->sectionId;

        if ( !isset( $content->fields['name'] ) )
            throw new PropertyNotFound( 'name', get_class( $content ) );
        elseif ( !isset( $content->fields['description'] ) )
            throw new PropertyNotFound( 'description', get_class( $content ) );

        $content->fields['name'] = $name;
        $content->fields['description'] = $description;
        $content->name = array( 'eng-GB' => $name );// @todo remove when name handler is in place

        return new Group( $this->repository->getContentService()->create( $content ) );
    }

    /**
     * Load a Group object by id
     *
     * @param mixed $id
     * @return \ezp\User\Group
     * @throws \ezp\Base\Exception\NotFound If group is not found
     * @throws \ezp\Base\Exception\NotFound If group is not found
     */
    public function loadGroup( $id )
    {
        $typeId = Configuration::getInstance( 'site' )->get( 'UserSettings', 'UserGroupClassID', 3 );
        $content = $this->repository->getContentService()->load( $id );
        if ( $content->typeId != $typeId )
            throw new NotFoundWithType( "User Group({$typeId})", $id );

        return new Group( $content );
    }

    /**
     * @param \ezp\User\GroupLocation $parent
     * @param \ezp\User|\ezp\User\Group $object
     * @return \ezp\User\UserLocation|\ezp\User\GroupLocation
     * @throws \ezp\Base\Exception\Logic If $object has not been persisted yet
     */
    public function assignGroupLocation( GroupLocation $parent, LocatableInterface $object )
    {
        $content = $object->getState( 'content' );
        if ( !$content instanceof Content  )
            throw new Logic( 'assignGroup', 'can not assign group to non created (persisted) object' );

        $newLocation = $content->addParent( $parent->getState( 'location' ) );
        $newLocation = $this->repository->getLocationService()->create( $newLocation );

        $locations = $object->getLocations();
        if( $object instanceof User )
            $locations[] = $newUserLocation = new UserLocation( $newLocation, $object );
        else
            $locations[] = $newUserLocation = new GroupLocation( $newLocation, $object );

        $object->setState( array( 'locations' => $locations ) );
        return $newUserLocation;
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
     * @param Role $role
     * @param Policy $policy
     */
    public function addPolicy( Role $role, Policy $policy )
    {
        $this->handler->userHandler()->addPolicy( $role->id, $policy->getState('properties') );
        $role->addPolicy( $policy );
    }

    /**
     * Remove a policy from a persisted role
     *
     * @param Role $role
     * @param Policy $policy
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
     * @param \ezp\Persistence\User $vo
     * @return \ezp\User
     */
    protected function buildUser( UserValueObject $vo, Content $content )
    {
        $do = new User();
        $do->setState(
            array(
                "properties" => $vo,
                "content" => $content,
                /*"groups" => new Lazy(
                    "ezp\\User\\Group",
                    $this,
                    $vo->id,
                    "loadGroupsByUserId"
                ),*/
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
                "policies" => $policies,
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
