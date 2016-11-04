<?php

/**
 * File containing the Role class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\RoleAssignment}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\RoleAssignment
 */
class RoleAssignment extends Values\User\RoleAssignment
{
    /**
     * the limitation of this role assignment.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    protected $limitation;

    /**
     * the role which is assigned to the user.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $role;

    /**
     * Returns the limitation of the user role assignment.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public function getRoleLimitation()
    {
        return $this->limitation;
    }

    /**
     * Returns the role to which the user is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
