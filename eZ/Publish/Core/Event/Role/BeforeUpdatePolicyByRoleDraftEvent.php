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
use UnexpectedValueException;

final class BeforeUpdatePolicyByRoleDraftEvent extends BeforeEvent
{
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

    public function getUpdatedPolicyDraft(): PolicyDraft
    {
        if (!$this->hasUpdatedPolicyDraft()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedPolicyDraft() or set it by setUpdatedPolicyDraft() before you call getter.', PolicyDraft::class));
        }

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
