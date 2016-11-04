<?php

/**
 * File containing the RestUserGroupRoleAssignment class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestUserGroupRoleAssignment view model.
 */
class RestUserGroupRoleAssignment extends RestValue
{
    /**
     * Role assignment.
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment
     */
    public $roleAssignment;

    /**
     * User group ID to which the role is assigned.
     *
     * @var mixed
     */
    public $id;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment $roleAssignment
     * @param mixed $id
     */
    public function __construct(UserGroupRoleAssignment $roleAssignment, $id)
    {
        $this->roleAssignment = $roleAssignment;
        $this->id = $id;
    }
}
