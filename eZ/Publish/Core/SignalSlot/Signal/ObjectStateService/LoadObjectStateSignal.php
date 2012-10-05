<?php
/**
 * LoadObjectStateSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadObjectStateSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class LoadObjectStateSignal extends Signal
{
    /**
     * StateId
     *
     * @var mixed
     */
    public $stateId;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $stateId
     */
    public function __construct( $stateId )
    {
        $this->stateId = $stateId;
    }
}

