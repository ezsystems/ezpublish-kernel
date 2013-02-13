<?php
/**
 * CreateObjectStateGroupSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateObjectStateGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class CreateObjectStateGroupSignal extends Signal
{
    /**
     * ObjectStateGroupId
     *
     * @var mixed
     */
    public $objectStateGroupId;
}

