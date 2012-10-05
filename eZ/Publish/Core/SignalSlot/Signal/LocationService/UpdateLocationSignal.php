<?php
/**
 * UpdateLocationSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateLocationSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class UpdateLocationSignal extends Signal
{
    /**
     * Location
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location;

    /**
     * LocationUpdateStruct
     *
     * @var eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct
     */
    public $locationUpdateStruct;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Location $location
     * @param eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct $locationUpdateStruct
     */
    public function __construct( $location, $locationUpdateStruct )
    {
        $this->location = $location;
        $this->locationUpdateStruct = $locationUpdateStruct;
    }
}

