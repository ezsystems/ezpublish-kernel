<?php
namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;

/**
 * This class is used to create a new role
 */
class RoleCreateStruct extends APIRoleCreateStruct
{
    /**
     *
     * adds a policy to this role
     * @param PolicyCreate $policyCreate
     */
    public function addPolicy( /*PolicyCreate*/ $policyCreate )
    {
        // @todo implement
    }

}
