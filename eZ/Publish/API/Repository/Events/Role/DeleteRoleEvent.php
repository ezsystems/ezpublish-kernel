<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class DeleteRoleEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\Role */
    private $role;

    public function __construct(
        Role $role
    ) {
        $this->role = $role;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}
