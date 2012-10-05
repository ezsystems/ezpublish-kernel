<?php
/**
 * LoadTrashItemSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadTrashItemSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\TrashService
 */
class LoadTrashItemSignal extends Signal
{
    /**
     * TrashItemId
     *
     * @var mixed
     */
    public $trashItemId;

    /**
     * Constructor
     *
     * Construct from signal values
     *
     * @param mixed $trashItemId
     */
    public function __construct( $trashItemId )
    {
        $this->trashItemId = $trashItemId;
    }
}

