<?php
/**
 * DeleteTrashItemSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteTrashItemSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\TrashService
 */
class DeleteTrashItemSignal extends Signal
{
    /**
     * TrashItem
     *
     * @var eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public $trashItem;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     */
    public function __construct( $trashItem )
    {
        $this->trashItem = $trashItem;
    }
}

