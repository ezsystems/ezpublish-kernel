<?php
/**
 * LoadLocationsSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadLocationsSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class LoadLocationsSignal extends Signal
{
    /**
     * ContentInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * RootLocation
     *
     * @var eZ\Publish\API\Repository\Values\Content\Location
     */
    public $rootLocation;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param eZ\Publish\API\Repository\Values\Content\Location $rootLocation
     */
    public function __construct( $contentInfo, $rootLocation )
    {
        $this->contentInfo = $contentInfo;
        $this->rootLocation = $rootLocation;
    }
}

