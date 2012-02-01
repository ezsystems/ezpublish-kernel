<?php
namespace eZ\Publish\API\Values\User;

use eZ\Publish\API\Values\ValueObject;
use eZ\Publish\API\Values\User\UserGroup;
/**
 * This class represents a user group to role assignment
 * @property-read \eZ\Publish\API\Values\User\UserGroup $userGroup calls getUserGroup()
 */
abstract class UserGroupRoleAssignment extends RoleAssignment
{
    /**
     * returns the user group to which the role is assigned to
     *
     * @return \eZ\Publish\API\Values\User\UserGroup
     */
    abstract function getUserGroup();
}
