<?php
/**
 * CreateLocationSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\LocationService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateLocationSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\LocationService
 */
class CreateLocationSignal extends Signal
{
    /**
     * ContentInfo
     *
     * @var eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public $contentInfo;

    /**
     * LocationCreateStruct
     *
     * @var eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public $locationCreateStruct;

}

