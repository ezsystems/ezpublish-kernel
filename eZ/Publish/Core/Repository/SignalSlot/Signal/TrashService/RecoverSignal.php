<?php
/**
 * RecoverSignal class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * RecoverSignal class
 * @package eZ\Publish\Core\Repository\SignalSlot\Signal\TrashService
 */
class RecoverSignal extends Signal
{
    /**
     * TrashItemId
     *
     * @var mixed
     */
    public $trashItemId;

    /**
     * NewParentLocationId
     *
     * @var mixed
     */
    public $newParentLocationId;

    /**
     * NewLocationId
     *
     * @var mixed
     */
    public $newLocationId;
}
