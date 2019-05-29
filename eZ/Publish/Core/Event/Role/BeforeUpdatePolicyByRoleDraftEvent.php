<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeUpdatePolicyByRoleDraftEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.role.update_policy_by_draft.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    private $roleDraft;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\PolicyDraft
     */
    private $policy;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    private $policyUpdateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\PolicyDraft|null
     */
    private $updatedPolicyDraft;

    public function __construct(RoleDraft $roleDraft, PolicyDraft $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        $this->roleDraft = $roleDraft;
        $this->policy = $policy;
        $this->policyUpdateStruct = $policyUpdateStruct;
    }

    public function getRoleDraft(): RoleDraft
    {
        return $this->roleDraft;
    }

    public function getPolicy(): PolicyDraft
    {
        return $this->policy;
    }

    public function getPolicyUpdateStruct(): PolicyUpdateStruct
    {
        return $this->policyUpdateStruct;
    }

    public function getUpdatedPolicyDraft(): ?PolicyDraft
    {
        return $this->updatedPolicyDraft;
    }

    public function setUpdatedPolicyDraft(?PolicyDraft $updatedPolicyDraft): void
    {
        $this->updatedPolicyDraft = $updatedPolicyDraft;
    }

    public function hasUpdatedPolicyDraft(): bool
    {
        return $this->updatedPolicyDraft instanceof PolicyDraft;
    }
}
