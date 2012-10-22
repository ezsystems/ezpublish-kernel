<?php
/**
 * SwapLocationSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * SwapLocationSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class SwapLocationSignal extends Signal
{
    /**
     * Location1Id
     *
     * @var mixed
     */
    public $location1Id;

    /**
     * Location2Id
     *
     * @var mixed
     */
    public $location2Id;

}

