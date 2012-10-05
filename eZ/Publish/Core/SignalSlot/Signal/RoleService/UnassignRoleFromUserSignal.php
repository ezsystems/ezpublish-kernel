<?php
/**
 * UnassignRoleFromUserSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UnassignRoleFromUserSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class UnassignRoleFromUserSignal extends Signal
{
    /**
     * Role
     *
     * @var eZ\Publish\API\Repository\Values\User\Role
     */
    public $role;

    /**
     * User
     *
     * @var eZ\Publish\API\Repository\Values\User\User
     */
    public $user;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\User\Role $role
     * @param eZ\Publish\API\Repository\Values\User\User $user
     */
    public function __construct( $role, $user )
    {
        $this->role = $role;
        $this->user = $user;
    }
}

