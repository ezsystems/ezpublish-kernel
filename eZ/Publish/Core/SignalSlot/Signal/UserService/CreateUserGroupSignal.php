<?php

/**
 * CreateUserGroupSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\UserService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateUserGroupSignal class.
 */
class CreateUserGroupSignal extends Signal
{
    /**
     * User Group ID.
     *
     * @var mixed
     */
    public $userGroupId;
}
