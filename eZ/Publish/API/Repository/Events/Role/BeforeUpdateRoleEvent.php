<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;

interface BeforeUpdateRoleEvent extends BeforeEvent
{
    public function getRole(): Role;

    public function getRoleUpdateStruct(): RoleUpdateStruct;

    public function getUpdatedRole(): Role;

    public function setUpdatedRole(?Role $updatedRole): void;

    public function hasUpdatedRole(): bool;
}
