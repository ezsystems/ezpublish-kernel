<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\AssignRoleToUserGroupEvent as AssignRoleToUserGroupEventInterface;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class AssignRoleToUserGroupEvent extends AfterEvent implements AssignRoleToUserGroupEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $role;

    /** @var \eZ\Publish\API\Repository\Values\User\UserGroup */
    private $userGroup;

    /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation */
    private $roleLimitation;

    public function __construct(
        Role $role,
        UserGroup $userGroup,
        ?RoleLimitation $roleLimitation = null
    ) {
        $this->role = $role;
        $this->userGroup = $userGroup;
        $this->roleLimitation = $roleLimitation;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getUserGroup(): UserGroup
    {
        return $this->userGroup;
    }

    public function getRoleLimitation(): ?RoleLimitation
    {
        return $this->roleLimitation;
    }
}
