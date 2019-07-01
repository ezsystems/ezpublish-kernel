<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Event\AfterEvent;

final class CreateRoleEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\RoleCreateStruct */
    private $roleCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft */
    private $roleDraft;

    public function __construct(
        RoleDraft $roleDraft,
        RoleCreateStruct $roleCreateStruct
    ) {
        $this->roleCreateStruct = $roleCreateStruct;
        $this->roleDraft = $roleDraft;
    }

    public function getRoleCreateStruct(): RoleCreateStruct
    {
        return $this->roleCreateStruct;
    }

    public function getRoleDraft(): RoleDraft
    {
        return $this->roleDraft;
    }
}
