<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Event\AfterEvent;

final class UnassignRoleFromUserEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $role;

    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $user;

    public function __construct(
        Role $role,
        User $user
    ) {
        $this->role = $role;
        $this->user = $user;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
