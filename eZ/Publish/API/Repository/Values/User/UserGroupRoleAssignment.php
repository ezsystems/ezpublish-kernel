<?php
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserGroup;
/**
 * This class represents a user group to role assignment
 */
abstract class UserGroupRoleAssignment extends RoleAssignment
{
    /**
     * returns the user group to which the role is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    abstract function getUserGroup();
}
