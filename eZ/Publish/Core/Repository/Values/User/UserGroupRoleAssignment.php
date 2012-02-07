<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\Core\Repository\Values\User\RoleAssignment;

/**
 * This class represents a user group to role assignment
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup user group to which the role is assigned to
 */
class UserGroupRoleAssignment extends RoleAssignment
{
    /**
     * user group to which the role is assigned to
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    protected $userGroup;

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
