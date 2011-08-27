<?php
/**
 * File contains User Service
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Service as BaseService,
    ezp\Base\Exception\NotFound,
    ezp\User,
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
     */
    public function create( User $user )
    {
        $struct = new UserValueObject();
        $this->fillStruct( $struct, $user );
        $vo = $this->handler->userHandler()->create( $struct );
        return $this->buildUser( $vo );
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
        return $this->buildUser( $this->handler->userHandler()->load( $id ) );
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
     * Crate a Role object
     *
     * @param \ezp\User\Role $user
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
     * Update a Role object
     *
     * @param \ezp\User\Role $user
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
     * @param \ezp\Persistence\User $vo
     * @return \ezp\User
     */
    protected function buildUser( UserValueObject $vo )
    {
        $do = new User();
        $do->setState(
            array(
                "properties" => $vo,
                /*"groups" => new Lazy(
                    "ezp\\User\\Group",
                    $this,
                    $vo->id,
                    "loadGroupsByUserId"
                ),
                "roles" => new Lazy(
                    "ezp\\User\\Group",
                    $this,
                    $vo->id,
                    "loadRolesByUserId"
                ),
                "policies" => new Lazy(
                    "ezp\\User\\Policy",
                    $this,
                    $vo->id,
                    "loadPoliciesByUserId"
                ),*/
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
     * @param \ezp\User\Role $role
     * @return \ezp\User\Policy
     */
    protected function buildPolicy( PolicyValueObject $vo, Role $role )
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
