<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

/**
 * This class represents a user group to role assignment
 * @property-read \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup calls getUserGroup()
 */
abstract class UserGroupRoleAssignment extends RoleAssignment
{
    /**
     * Returns the user group to which the role is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    abstract function getUserGroup();
}
