<?php
/**
 * AddPolicySignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AddPolicySignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class AddPolicySignal extends Signal
{
    /**
     * Role
     *
     * @var eZ\Publish\API\Repository\Values\User\Role
     */
    public $role;

    /**
     * PolicyCreateStruct
     *
     * @var eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public $policyCreateStruct;

}

