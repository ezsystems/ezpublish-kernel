<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\Core\Repository\Values\User\RoleAssignment;

/**
 *
 * This classs represents a user to role assignment
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\User $user calls getUser()
 */
class UserRoleAssignment extends RoleAssignment
{
    /**
     * user group to which the role is assigned to
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $user;

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
