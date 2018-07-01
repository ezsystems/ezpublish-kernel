<?php

/**
 * UnAssignUserFromUserGroupSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\UserService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UnAssignUserFromUserGroupSignal class.
 */
class UnAssignUserFromUserGroupSignal extends Signal
{
    /**
     * UserId.
     *
     * @var mixed
     */
    public $userId;

    /**
     * UserGroupId.
     *
     * @var mixed
     */
    public $userGroupId;
}
