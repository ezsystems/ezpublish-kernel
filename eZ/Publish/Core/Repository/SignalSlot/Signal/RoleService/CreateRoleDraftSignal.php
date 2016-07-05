<?php

/**
 * CreateRoleDraftSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * CreateRoleDraftSignal class.
 */
class CreateRoleDraftSignal extends Signal
{
    /**
     * RoleId.
     *
     * @var mixed
     */
    public $roleId;
}
