<?php
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * 
 * This classs represents a user to role assignment 
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\User $user calls getUser()
 */
abstract class UserRoleAssignment extends RoleAssignment
{
    /**
     * returns the user to which the role is assigned to
     * 
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    abstract public function getUser();
}
