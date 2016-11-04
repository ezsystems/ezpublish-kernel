<?php

/**
 * RemovePolicyByRoleDraftSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * RemovePolicyByRoleDraftSignal class.
 */
class RemovePolicyByRoleDraftSignal extends Signal
{
    /**
     * RoleId.
     *
     * @var mixed
     */
    public $roleId;

    /**
     * PolicyId.
     *
     * @var mixed
     */
    public $policyId;
}
