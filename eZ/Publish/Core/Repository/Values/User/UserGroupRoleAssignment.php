<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment as APIUserGroupRoleAssignment;

/**
 * This class represents a user group to role assignment
 */
class UserGroupRoleAssignment extends APIUserGroupRoleAssignment
{
    /**
     * the limitation of this role assignment
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    protected $limitation;

    /**
     * the role which is assigned to the user group
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $role;

    /**
     * user group to which the role is assigned to
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    protected $userGroup;

    /**
     * returns the limitation of the role assignment
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public function getRoleLimitation()
    {
        return $this->limitation;
    }

    /**
     * returns the role to which the user group is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * returns the user group to which the role is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }
}
