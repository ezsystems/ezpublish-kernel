<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\User\UserGroup;

interface BeforeMoveUserGroupEvent extends BeforeEvent
{
    public function getUserGroup(): UserGroup;

    public function getNewParent(): UserGroup;
}
