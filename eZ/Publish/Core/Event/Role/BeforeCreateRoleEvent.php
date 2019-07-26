<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\BeforeCreateRoleEvent as BeforeCreateRoleEventInterface;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateRoleEvent extends BeforeEvent implements BeforeCreateRoleEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\RoleCreateStruct */
    private $roleCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft|null */
    private $roleDraft;

    public function __construct(RoleCreateStruct $roleCreateStruct)
    {
        $this->roleCreateStruct = $roleCreateStruct;
    }

    public function getRoleCreateStruct(): RoleCreateStruct
    {
        return $this->roleCreateStruct;
    }

    public function getRoleDraft(): RoleDraft
    {
        if (!$this->hasRoleDraft()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasRoleDraft() or set it by setRoleDraft() before you call getter.', RoleDraft::class));
        }

        return $this->roleDraft;
    }

    public function setRoleDraft(?RoleDraft $roleDraft): void
    {
        $this->roleDraft = $roleDraft;
    }

    public function hasRoleDraft(): bool
    {
        return $this->roleDraft instanceof RoleDraft;
    }
}
