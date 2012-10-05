<?php
/**
 * UpdateObjectStateSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateObjectStateSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class UpdateObjectStateSignal extends Signal
{
    /**
     * ObjectState
     *
     * @var eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public $objectState;

    /**
     * ObjectStateUpdateStruct
     *
     * @var eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct
     */
    public $objectStateUpdateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct $objectStateUpdateStruct
     */
    public function __construct( $objectState, $objectStateUpdateStruct )
    {
        $this->objectState = $objectState;
        $this->objectStateUpdateStruct = $objectStateUpdateStruct;
    }
}

