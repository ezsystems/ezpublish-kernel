<?php
/**
 * LoadObjectStateGroupsSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadObjectStateGroupsSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class LoadObjectStateGroupsSignal extends Signal
{
    /**
     * Offset
     *
     * @var mixed
     */
    public $offset;

    /**
     * Limit
     *
     * @var mixed
     */
    public $limit;

}

