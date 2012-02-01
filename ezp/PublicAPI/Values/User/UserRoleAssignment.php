<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\ValueObject;

/**
 * 
 * This classs represents a user to role assignment 
 *
 * @property-read \ezp\PublicAPI\Values\User\User $user calls getUser()
 */
abstract class UserRoleAssignment extends RoleAssignment
{
    /**
     * returns the user to which the role is assigned to
     * 
     * @return \ezp\PublicAPI\Values\UserUser
     */
    public abstract function getUser();
}
