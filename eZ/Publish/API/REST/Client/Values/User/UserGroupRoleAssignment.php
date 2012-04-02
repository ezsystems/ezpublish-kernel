<?php
/**
 * File containing the UserGroupRoleAssignment class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Values\User;


/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment
 */
class UserGroupRoleAssignment extends \eZ\Publish\API\Repository\UserGroupRoleAssignment
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $role;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    protected $userGroup;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    protected $limitation;

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
     * returns the role to which the user or user group is assigned to
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
    function getUserGroup()
    {
        return $this->userGroup;
    }

}