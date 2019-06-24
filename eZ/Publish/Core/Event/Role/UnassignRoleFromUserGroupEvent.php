<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Event\AfterEvent;

final class UnassignRoleFromUserGroupEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    private $role;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private $userGroup;

    public function __construct(
        Role $role,
        UserGroup $userGroup
    ) {
        $this->role = $role;
        $this->userGroup = $userGroup;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }
}
