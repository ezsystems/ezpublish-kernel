<?php
/**
 * AssignRoleToUserGroupSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AssignRoleToUserGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class AssignRoleToUserGroupSignal extends Signal
{
    /**
     * Role
     *
     * @var eZ\Publish\API\Repository\Values\User\Role
     */
    public $role;

    /**
     * UserGroup
     *
     * @var eZ\Publish\API\Repository\Values\User\UserGroup
     */
    public $userGroup;

    /**
     * RoleLimitation
     *
     * @var eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public $roleLimitation;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\User\Role $role
     * @param eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation
     */
    public function __construct( $role, $userGroup, $roleLimitation )
    {
        $this->role = $role;
        $this->userGroup = $userGroup;
        $this->roleLimitation = $roleLimitation;
    }
}

