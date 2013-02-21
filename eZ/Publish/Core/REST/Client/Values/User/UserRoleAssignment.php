<?php
/**
 * File containing the UserRoleAssignment class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\UserRoleAssignment}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\UserRoleAssignment
 */
class UserRoleAssignment extends \eZ\Publish\API\Repository\Values\User\UserRoleAssignment
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $role;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $user;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    protected $limitation;

    /**
     * Returns the limitation of the role assignment
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public function getRoleLimitation()
    {
        return $this->limitation;
    }

    /**
     * Returns the role to which the user or user group is assigned to
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns the user to which the role is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    function getUser()
    {
        return $this->user;
    }

}
