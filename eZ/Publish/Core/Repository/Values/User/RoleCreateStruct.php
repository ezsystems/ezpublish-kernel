<?php

namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct,
    eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;

/**
 * This class is used to create a new role
 */
class RoleCreateStruct extends APIRoleCreateStruct
{
    /**
     * Policies associated with the role
     *
     * @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    private $policies = array();

    /**
     * Returns policies associated with the role
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct[]
     */
    public function getPolicies()
    {
        return $this->policies;
    }

    /**
     * Adds a policy to this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreate
     */
    public function addPolicy( PolicyCreateStruct $policyCreate )
    {
        $this->policies[] = $policyCreate;
    }
}
