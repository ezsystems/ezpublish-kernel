<?php
/**
 * LoadPoliciesByUserIdSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadPoliciesByUserIdSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class LoadPoliciesByUserIdSignal extends Signal
{
    /**
     * UserId
     *
     * @var mixed
     */
    public $userId;

}

