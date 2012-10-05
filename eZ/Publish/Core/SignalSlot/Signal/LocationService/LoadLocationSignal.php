<?php
/**
 * LoadLocationSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadLocationSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class LoadLocationSignal extends Signal
{
    /**
     * LocationId
     *
     * @var mixed
     */
    public $locationId;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $locationId
     */
    public function __construct( $locationId )
    {
        $this->locationId = $locationId;
    }
}

