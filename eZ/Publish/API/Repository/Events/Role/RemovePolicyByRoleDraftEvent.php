<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class RemovePolicyByRoleDraftEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft */
    private $roleDraft;

    /** @var \eZ\Publish\API\Repository\Values\User\PolicyDraft */
    private $policyDraft;

    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft */
    private $updatedRoleDraft;

    public function __construct(
        RoleDraft $updatedRoleDraft,
        RoleDraft $roleDraft,
        PolicyDraft $policyDraft
    ) {
        $this->roleDraft = $roleDraft;
        $this->policyDraft = $policyDraft;
        $this->updatedRoleDraft = $updatedRoleDraft;
    }

    public function getRoleDraft(): RoleDraft
    {
        return $this->roleDraft;
    }

    public function getPolicyDraft(): PolicyDraft
    {
        return $this->policyDraft;
    }

    public function getUpdatedRoleDraft(): RoleDraft
    {
        return $this->updatedRoleDraft;
    }
}
