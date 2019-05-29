<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Event\AfterEvent;

final class CreateRoleDraftEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.role.draft_create';

    /**
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    private $role;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    private $roleDraft;

    public function __construct(
        RoleDraft $roleDraft,
        Role $role
    ) {
        $this->role = $role;
        $this->roleDraft = $roleDraft;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getRoleDraft(): RoleDraft
    {
        return $this->roleDraft;
    }
}
