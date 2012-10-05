<?php
/**
 * FindTrashItemsSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\TrashService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * FindTrashItemsSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\TrashService
 */
class FindTrashItemsSignal extends Signal
{
    /**
     * Query
     *
     * @var eZ\Publish\API\Repository\Values\Content\Query
     */
    public $query;

}

