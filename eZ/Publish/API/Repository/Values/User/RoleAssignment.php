<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\RoleAssignment class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This value object represents an assignment od a user or user group to a role including a limitation.
 *
 * @property-read \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitation the limitation of this role assignment
 * @property-read \eZ\Publish\API\Repository\Values\User\Role $role the role which is assigned to the user or user group
 */
abstract class RoleAssignment extends ValueObject
{
    /**
     * The unique id of the role assignment.
     *
     * @var mixed
     */
    protected $id;

    /**
     * Returns the limitation of the role assignment.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation|null
     */
    abstract public function getRoleLimitation();

    /**
     * Returns the role to which the user or user group is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    abstract public function getRole();
}
