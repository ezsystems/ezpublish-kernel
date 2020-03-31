<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleCopyStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class CopyRoleEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $copiedRole;

    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $role;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleCopyStruct */
    private $roleCopyStruct;

    public function __construct(
        Role $copiedRole,
        Role $role,
        RoleCopyStruct $roleCopyStruct
    ) {
        $this->copiedRole = $copiedRole;
        $this->role = $role;
        $this->roleCopyStruct = $roleCopyStruct;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getCopiedRole(): Role
    {
        return $this->copiedRole;
    }

    public function getRoleCopyStruct(): RoleCopyStruct
    {
        return $this->roleCopyStruct;
    }
}
