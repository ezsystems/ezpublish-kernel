<?php
/**
 * CopyContentSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CopyContentSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class CopyContentSignal extends Signal
{
    /**
     * Source Content ID
     *
     * @var mixed
     */
    public $srcContentId;

    /**
     * Source Version Number
     *
     * @var int|null
     */
    public $srcVersionNo;

    /**
     * Destination Content ID
     *
     * @var mixed
     */
    public $dstContentId;

    /**
     * Destination Version Number
     *
     * @var int
     */
    public $dstVersionNo;


    /**
     * Destination Parent Location ID
     *
     * @var mixed
     */
    public $dstParentLocationId;
}
