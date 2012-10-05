<?php
/**
 * MoveSubtreeSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * MoveSubtreeSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class MoveSubtreeSignal extends Signal
{
    /**
     * Location
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location;

    /**
     * NewParentLocation
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $newParentLocation;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Location $location
     * @param eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    public function __construct( $location, $newParentLocation )
    {
        $this->location = $location;
        $this->newParentLocation = $newParentLocation;
    }
}

