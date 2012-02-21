<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\UserRoleAssignment as APIUserRoleAssignment;

/**
 *
 * This class represents a user to role assignment
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitation the limitation of this role assignment
 * @property-read \eZ\Publish\API\Repository\Values\User\Role $role the role which is assigned to the user
 * @property-read \eZ\Publish\API\Repository\Values\User\User $user user to which the role is assigned to
 */
class UserRoleAssignment extends APIUserRoleAssignment
{
    /**
     * the limitation of this role assignment
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    protected $limitation;

    /**
     * the role which is assigned to the user
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $role;

    /**
     * user to which the role is assigned to
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $user;

    /**
     * returns the limitation of the user role assignment
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public function getRoleLimitation()
    {
        return $this->limitation;
    }

    /**
     * returns the role to which the user is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * returns the user to which the role is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
