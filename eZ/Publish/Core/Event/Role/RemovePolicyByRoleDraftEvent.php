<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Event\AfterEvent;

final class RemovePolicyByRoleDraftEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.role.remove_policy_by_draft';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    private $roleDraft;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\PolicyDraft
     */
    private $policyDraft;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
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
