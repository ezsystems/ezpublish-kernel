<?php
/**
 * RecoverSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * RecoverSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\TrashService
 */
class RecoverSignal extends Signal
{
    /**
     * TrashItem
     *
     * @var eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public $trashItem;

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
     * @param eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     * @param eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    public function __construct( $trashItem, $newParentLocation )
    {
        $this->trashItem = $trashItem;
        $this->newParentLocation = $newParentLocation;
    }
}

