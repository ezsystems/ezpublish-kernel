<?php
/**
 * DeleteTrashItemSignal class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * DeleteTrashItemSignal class
 * @package eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService
 */
class DeleteTrashItemSignal extends Signal
{
    /**
     * TrashItemId
     *
     * @var mixed
     */
    public $trashItemId;
}
