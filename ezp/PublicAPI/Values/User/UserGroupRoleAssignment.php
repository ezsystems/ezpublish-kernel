<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\ValueObject;
use ezp\PublicAPI\Values\User\UserGroup;
/**
 * This class represents a user group to role assignment
 * @property-read \ezp\PublicAPI\Values\User\UserGroup $userGroup calls getUserGroup()
 */
abstract class UserGroupRoleAssignment extends RoleAssignment
{
    /**
     * returns the user group to which the role is assigned to
     *
     * @return \ezp\PublicAPI\Values\User\UserGroup
     */
    abstract function getUserGroup();
}
