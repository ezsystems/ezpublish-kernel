<?php
/**
 * SetContentStateSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ObjectStateService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * SetContentStateSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ObjectStateService
 */
class SetContentStateSignal extends Signal
{
    /**
     * ContentId
     *
     * @var mixed
     */
    public $contentId;

    /**
     * ObjectStateGroupId
     *
     * @var mixed
     */
    public $objectStateGroupId;

    /**
     * ObjectStateId
     *
     * @var mixed
     */
    public $objectStateId;
}
