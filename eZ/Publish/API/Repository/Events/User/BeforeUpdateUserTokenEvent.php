<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;

interface BeforeUpdateUserTokenEvent
{
    public function getUser(): User;

    public function getUserTokenUpdateStruct(): UserTokenUpdateStruct;

    public function getUpdatedUser(): User;

    public function setUpdatedUser(?User $updatedUser): void;

    public function hasUpdatedUser(): bool;
}
