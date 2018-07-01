<?php

/**
 * File containing the RoleList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Role list view model.
 */
class RoleList extends RestValue
{
    /**
     * Roles.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role[]
     */
    public $roles;

    /**
     * Path used to load the list of roles.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role[] $roles
     * @param string $path
     */
    public function __construct(array $roles, $path)
    {
        $this->roles = $roles;
        $this->path = $path;
    }
}
