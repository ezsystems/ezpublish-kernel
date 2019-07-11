<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;

interface BeforeUpdateUserGroupEvent extends BeforeEvent
{
    public function getUserGroup(): UserGroup;

    public function getUserGroupUpdateStruct(): UserGroupUpdateStruct;

    public function getUpdatedUserGroup(): UserGroup;

    public function setUpdatedUserGroup(?UserGroup $updatedUserGroup): void;

    public function hasUpdatedUserGroup(): bool;
}
