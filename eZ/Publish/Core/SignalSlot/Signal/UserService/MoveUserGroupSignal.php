<?php

/**
 * MoveUserGroupSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\UserService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * MoveUserGroupSignal class.
 */
class MoveUserGroupSignal extends Signal
{
    /**
     * UserGroupId.
     *
     * @var mixed
     */
    public $userGroupId;

    /**
     * NewParentId.
     *
     * @var mixed
     */
    public $newParentId;
}
