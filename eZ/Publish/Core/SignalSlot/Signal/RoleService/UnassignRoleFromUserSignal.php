<?php

/**
 * UnassignRoleFromUserSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UnassignRoleFromUserSignal class.
 */
class UnassignRoleFromUserSignal extends Signal
{
    /**
     * RoleId.
     *
     * @var mixed
     */
    public $roleId;

    /**
     * UserId.
     *
     * @var mixed
     */
    public $userId;
}
