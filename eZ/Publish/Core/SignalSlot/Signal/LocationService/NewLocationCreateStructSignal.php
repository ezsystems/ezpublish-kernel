<?php
/**
 * NewLocationCreateStructSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * NewLocationCreateStructSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class NewLocationCreateStructSignal extends Signal
{
    /**
     * ParentLocationId
     *
     * @var mixed
     */
    public $parentLocationId;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $parentLocationId
     */
    public function __construct( $parentLocationId )
    {
        $this->parentLocationId = $parentLocationId;
    }
}

