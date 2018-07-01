<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

/**
 * This class represents a user group to role assignment.
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup calls getUserGroup()
 */
abstract class UserGroupRoleAssignment extends RoleAssignment
{
    /**
     * Returns the user group to which the role is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    abstract public function getUserGroup();
}
