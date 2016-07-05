<?php

/**
 * AddPolicyByRoleDraftSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\RoleService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * AddPolicyByRoleDraftSignal class.
 */
class AddPolicyByRoleDraftSignal extends Signal
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
