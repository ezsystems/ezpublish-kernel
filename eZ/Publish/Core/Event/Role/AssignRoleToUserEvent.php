<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\AssignRoleToUserEvent as AssignRoleToUserEventInterface;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class AssignRoleToUserEvent extends AfterEvent implements AssignRoleToUserEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $role;

    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $user;

    /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation */
    private $roleLimitation;

    public function __construct(
        Role $role,
        User $user,
        ?RoleLimitation $roleLimitation = null
    ) {
        $this->role = $role;
        $this->user = $user;
        $this->roleLimitation = $roleLimitation;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRoleLimitation(): ?RoleLimitation
    {
        return $this->roleLimitation;
    }
}
