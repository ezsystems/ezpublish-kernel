<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeAddPolicyByRoleDraftEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft */
    private $roleDraft;

    /** @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct */
    private $policyCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft|null */
    private $updatedRoleDraft;

    public function __construct(RoleDraft $roleDraft, PolicyCreateStruct $policyCreateStruct)
    {
        $this->roleDraft = $roleDraft;
        $this->policyCreateStruct = $policyCreateStruct;
    }

    public function getRoleDraft(): RoleDraft
    {
        return $this->roleDraft;
    }

    public function getPolicyCreateStruct(): PolicyCreateStruct
    {
        return $this->policyCreateStruct;
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
