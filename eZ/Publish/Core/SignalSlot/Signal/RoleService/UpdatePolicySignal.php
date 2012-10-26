<?php
/**
 * UpdatePolicySignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\RoleService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdatePolicySignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\RoleService
 */
class UpdatePolicySignal extends Signal
{
    /**
     * PolicyId
     *
     * @var mixed
     */
    public $policyId;
}
