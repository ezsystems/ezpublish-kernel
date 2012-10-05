<?php
/**
 * SetPriorityOfObjectStateSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * SetPriorityOfObjectStateSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class SetPriorityOfObjectStateSignal extends Signal
{
    /**
     * ObjectState
     *
     * @var eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public $objectState;

    /**
     * Priority
     *
     * @var mixed
     */
    public $priority;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param mixed $priority
     */
    public function __construct( $objectState, $priority )
    {
        $this->objectState = $objectState;
        $this->priority = $priority;
    }
}

