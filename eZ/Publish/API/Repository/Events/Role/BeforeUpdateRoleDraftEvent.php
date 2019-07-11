<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;

interface BeforeUpdateRoleDraftEvent extends BeforeEvent
{
    public function getRoleDraft(): RoleDraft;

    public function getRoleUpdateStruct(): RoleUpdateStruct;

    public function getUpdatedRoleDraft(): RoleDraft;

    public function setUpdatedRoleDraft(?RoleDraft $updatedRoleDraft): void;

    public function hasUpdatedRoleDraft(): bool;
}
