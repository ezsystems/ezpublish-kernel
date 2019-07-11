<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleDraft;

interface BeforeCreateRoleDraftEvent extends BeforeEvent
{
    public function getRole(): Role;

    public function getRoleDraft(): RoleDraft;

    public function setRoleDraft(?RoleDraft $roleDraft): void;

    public function hasRoleDraft(): bool;
}
