<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\Role class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\Role as APIRole;

/**
 * This class represents a role.
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\Policy[] $policies Policies assigned to this role
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class Role extends APIRole
{
    /**
     * Policies assigned to this role.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    protected $policies = [];

    /**
     * Returns the list of policies of this role.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function getPolicies()
    {
        return $this->policies;
    }
}
