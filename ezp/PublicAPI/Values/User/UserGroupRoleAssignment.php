<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\ValueObject;
use ezp\PublicAPI\Values\User\UserGroup;

abstract class UserGroupRoleAssignment extends RoleAssignment {
    
    /**
     * returns the user group to which the role is assigned to
     * @return UserGroup
     */
    abstract function getUserGroup();
    
    
}