<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\API\Repository\Values\User\UserGroup;

interface BeforeDeleteUserGroupEvent
{
    public function getUserGroup(): UserGroup;

    public function getLocations(): array;

    public function setLocations(?array $locations): void;

    public function hasLocations(): bool;
}
