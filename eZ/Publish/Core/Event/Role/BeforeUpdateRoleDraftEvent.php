<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\BeforeUpdateRoleDraftEvent as BeforeUpdateRoleDraftEventInterface;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforeUpdateRoleDraftEvent extends Event implements BeforeUpdateRoleDraftEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft */
    private $roleDraft;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct */
    private $roleUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft|null */
    private $updatedRoleDraft;

    public function __construct(RoleDraft $roleDraft, RoleUpdateStruct $roleUpdateStruct)
    {
        $this->roleDraft = $roleDraft;
        $this->roleUpdateStruct = $roleUpdateStruct;
    }

    public function getRoleDraft(): RoleDraft
    {
        return $this->roleDraft;
    }

    public function getRoleUpdateStruct(): RoleUpdateStruct
    {
        return $this->roleUpdateStruct;
    }

    public function getUpdatedRoleDraft(): RoleDraft
    {
        if (!$this->hasUpdatedRoleDraft()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedRoleDraft() or set it by setUpdatedRoleDraft() before you call getter.', RoleDraft::class));
        }

        return $this->updatedRoleDraft;
    }

    public function setUpdatedRoleDraft(?RoleDraft $updatedRoleDraft): void
    {
        $this->updatedRoleDraft = $updatedRoleDraft;
    }

    public function hasUpdatedRoleDraft(): bool
    {
        return $this->updatedRoleDraft instanceof RoleDraft;
    }
}
