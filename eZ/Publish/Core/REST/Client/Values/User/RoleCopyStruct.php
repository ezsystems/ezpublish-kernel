<?php

/**
 * File containing the RoleCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values;
use eZ\Publish\API\Repository\Values\User\RoleCopyStruct as APIRoleCopyStruct;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\RoleCopyStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\RoleCopyStruct
 */
class RoleCopyStruct extends APIRoleCopyStruct
{
    /** @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[] */
    private $policies = [];

    /**
     * Instantiates a role copy class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(['newIdentifier' => $name]);
    }

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
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     */
    public function addPolicy(Values\User\PolicyCreateStruct $policyCreateStruct)
    {
        $this->policies[] = $policyCreateStruct;
    }
}
