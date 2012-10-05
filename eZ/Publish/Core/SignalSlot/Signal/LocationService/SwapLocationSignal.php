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
     * Location1
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location1;

    /**
     * Location2
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location2;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\Location $location1
     * @param eZ\Publish\API\Repository\Values\Content\Location $location2
     */
    public function __construct( $location1, $location2 )
    {
        $this->location1 = $location1;
        $this->location2 = $location2;
    }
}

