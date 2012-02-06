<?php

namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;

/**
 * This class represents a role
 *
 * @property-read int $id the internal id of the role
 * @property-read string $name the name of the role
 * @property-read string $description the description of the role
 * @property-read array $policies an array of the policies {@link \eZ\Publish\API\Repository\Values\User\Policy} of the role.
 */
class Role extends APIRole
{
    /**
     * returns the list of policies of this role
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\UserPolicy}
     */
    public function getPolicies()
    {

    }

    /**
     * returns the policy for the given module and function
     * @return \eZ\Publish\API\Repository\Values\UserPolicy
     */
    public function getPolicy( $module, $function )
    {
        // @todo implement
    }
}
