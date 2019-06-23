<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\RoleCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;

/**
 * This class is used to create a new role.
 *
 * @internal Meant for internal use by Repository, type hint against API instead.
 */
class RoleCreateStruct extends APIRoleCreateStruct
{
    /**
     * Policies associated with the role.
     *
     * @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    protected $policies = [];

    /**
     * Returns policies associated with the role.
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    public function getPolicies()
    {
        return $this->policies;
    }

    /**
     * Adds a policy to this role.
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreate
     */
    public function addPolicy(APIPolicyCreateStruct $policyCreate)
    {
        $this->policies[] = $policyCreate;
    }
}
