<?php

namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\ValueObject;
use ezp\PublicAPI\Values\User\Limitation\RoleLimitation;
use ezp\PublicAPI\Values\User\Role;

/**
 * This value object represents an assignment od a user or user group to a role inlcuding a limitation
 * 
 * @property-read \ezp\PublicAPI\Values\User\Limitation\RoleLimitation $limitation the limitation of this role assignment
 * @property-read \ezp\PublicAPI\Values\User\Role $role the role which is assigned to the user or user group
 */
abstract class RoleAssignment extends ValueObject
{
    /**
     * returns the limitation of the role assignment
     * 
     * @return RoleLimitation
     */
    public function getLimitation();

    /**
     * returns the role to which the user or user group is assigned to
     * @return Role
     */
    abstract function getRole();
}
