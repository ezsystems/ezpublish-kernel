<?php

namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;

/**
 * This class is used to create a new role
 */
class RoleCreateStruct extends APIRoleCreateStruct
{
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
