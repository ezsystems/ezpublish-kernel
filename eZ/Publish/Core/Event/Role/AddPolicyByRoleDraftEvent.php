<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Event\AfterEvent;

final class AddPolicyByRoleDraftEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.role.add_policy_by_draft';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    private $roleDraft;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    private $policyCreateStruct;

    private $updatedRoleDraft;

    public function __construct(
        RoleDraft $updatedRoleDraft,
        RoleDraft $roleDraft,
        PolicyCreateStruct $policyCreateStruct
    ) {
        $this->roleDraft = $roleDraft;
        $this->policyCreateStruct = $policyCreateStruct;
        $this->updatedRoleDraft = $updatedRoleDraft;
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
        return $this->updatedRoleDraft;
    }
}
