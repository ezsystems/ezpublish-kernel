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
     * Policy
     *
     * @var eZ\Publish\API\Repository\Values\User\Policy
     */
    public $policy;

    /**
     * PolicyUpdateStruct
     *
     * @var eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public $policyUpdateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\User\Policy $policy
     * @param eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     */
    public function __construct( $policy, $policyUpdateStruct )
    {
        $this->policy = $policy;
        $this->policyUpdateStruct = $policyUpdateStruct;
    }
}

