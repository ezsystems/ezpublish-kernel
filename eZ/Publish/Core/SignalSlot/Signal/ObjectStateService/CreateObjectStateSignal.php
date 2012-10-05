<?php
/**
 * CreateObjectStateSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateObjectStateSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class CreateObjectStateSignal extends Signal
{
    /**
     * ObjectStateGroup
     *
     * @var eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public $objectStateGroup;

    /**
     * ObjectStateCreateStruct
     *
     * @var eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct
     */
    public $objectStateCreateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct $objectStateCreateStruct
     */
    public function __construct( $objectStateGroup, $objectStateCreateStruct )
    {
        $this->objectStateGroup = $objectStateGroup;
        $this->objectStateCreateStruct = $objectStateCreateStruct;
    }
}

