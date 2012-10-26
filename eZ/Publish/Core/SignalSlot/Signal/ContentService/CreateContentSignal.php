<?php
/**
 * CreateContentSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateContentSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class CreateContentSignal extends Signal
{
    /**
     * Content ID
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Version Number
     *
     * @var int
     */
    public $versionNo;
}
