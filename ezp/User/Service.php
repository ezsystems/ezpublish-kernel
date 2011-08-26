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
    ezp\Persistence\User as UserValueObject,
    ezp\Persistence\User\Role as RoleValueObject,
    ezp\Persistence\User\Policy as PolicyValueObject;

/**
 * User Service, extends repository with user specific operations
 *
 */
class Service extends BaseService
{
    /**
     * Crate a Content Type Group object
     *
     * @param \ezp\User $group
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
     * Get an User object by id
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
                "groups" => new Lazy(
                    "ezp\\User\\Group",
                    $this,
                    $vo->id,
                    "loadPoliciesByUserId"
                ),*/
            )
        );
        return $do;
    }
}
