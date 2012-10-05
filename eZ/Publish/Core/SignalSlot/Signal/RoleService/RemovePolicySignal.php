<?php
/**
 * RemovePolicySignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * RemovePolicySignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class RemovePolicySignal extends Signal
{
    /**
     * Role
     *
     * @var eZ\Publish\API\Repository\Values\User\Role
     */
    public $role;

    /**
     * Policy
     *
     * @var eZ\Publish\API\Repository\Values\User\Policy
     */
    public $policy;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\User\Role $role
     * @param eZ\Publish\API\Repository\Values\User\Policy $policy
     */
    public function __construct( $role, $policy )
    {
        $this->role = $role;
        $this->policy = $policy;
    }
}

