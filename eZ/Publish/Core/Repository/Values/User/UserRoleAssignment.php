<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation as APIRoleLimitation;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\User\UserRoleAssignment as APIUserRoleAssignment;

/**
 * This class represents a user to role assignment.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class UserRoleAssignment extends APIUserRoleAssignment
{
    /**
     * the limitation of this role assignment.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation|null
     */
    protected $limitation;

    /**
     * the role which is assigned to the user.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $role;

    /**
     * user to which the role is assigned to.
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $user;

    /**
     * Returns the limitation of the user role assignment.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation|null
     */
    public function getRoleLimitation(): ?APIRoleLimitation
    {
        return $this->limitation;
    }

    /**
     * Returns the role to which the user is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function getRole(): APIRole
    {
        return $this->role;
    }

    /**
     * Returns the user to which the role is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getUser(): APIUser
    {
        return $this->user;
    }
}
