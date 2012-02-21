<?php

namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;

/**
 * This class represents a role
 *
 * @property-read array $policies Policies assigned to this role
 */
class Role extends APIRole
{
    /**
     * Policies assigned to this role
     *
     * @var array
     */
    protected $policies = array();

    /**
     * returns the list of policies of this role
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Policy}
     */
    public function getPolicies()
    {
        return $this->policies;
    }
}
