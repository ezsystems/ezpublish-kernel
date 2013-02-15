<?php
/**
 * AssignRoleToUserSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AssignRoleToUserSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class AssignRoleToUserSignal extends Signal
{
    /**
     * RoleId
     *
     * @var mixed
     */
    public $roleId;

    /**
     * UserId
     *
     * @var mixed
     */
    public $userId;

    /**
     * RoleLimitation
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public $roleLimitation;
}
