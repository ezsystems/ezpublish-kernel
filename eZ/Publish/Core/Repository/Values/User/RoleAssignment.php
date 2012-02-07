<?php

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\RoleAssignment as APIRoleAssignment;

/**
 * This value object represents an assignment of a user or user group to a role, including a limitation
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitation the limitation of this role assignment
 * @property-read \eZ\Publish\API\Repository\Values\User\Role $role the role which is assigned to the user or user group
 */
class RoleAssignment extends APIRoleAssignment
{
    /**
     * the limitation of this role assignment
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    protected $limitation;

    /**
     * the role which is assigned to the user or user group
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $role;

    /**
     * returns the limitation of the role assignment
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public function getLimitation()
    {
        return $this->limitation;
    }

    /**
     * returns the role to which the user or user group is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
