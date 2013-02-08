<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

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
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation|null
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
     * Returns the limitation of the role assignment
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation|null
     */
    public function getRoleLimitation()
    {
        return $this->limitation;
    }

    /**
     * Returns the role to which the user group is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns the user group to which the role is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }
}
