<?php
/**
 * UpdateUserGroupSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\UserService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateUserGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\UserService
 */
class UpdateUserGroupSignal extends Signal
{
    /**
     * UserGroupId
     *
     * @var mixed
     */
    public $userGroupId;
}
