<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;

interface BeforeCreateUserGroupEvent extends BeforeEvent
{
    public function getUserGroupCreateStruct(): UserGroupCreateStruct;

    public function getParentGroup(): UserGroup;

    public function getUserGroup(): UserGroup;

    public function setUserGroup(?UserGroup $userGroup): void;

    public function hasUserGroup(): bool;
}
