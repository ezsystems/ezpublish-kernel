<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\ValueObject;
use ezp\PublicAPI\Values\User\User;

abstract class UserRoleAssignment extends RoleAssignment {
    
    /**
     * returns the user to which the role is assigned to
     * @return User
     */
    abstract function getUser();
    
    
}
