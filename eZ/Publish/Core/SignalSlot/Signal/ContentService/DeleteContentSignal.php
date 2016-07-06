<?php
/**
 * DeleteContentSignal class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteContentSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 *
 * If Content has locations the systems will emmit {@link ..\LocationServide\DeleteSubtreeSignal} for each assigned location.
 */
class DeleteContentSignal extends Signal
{
    /**
     * ContentId
     *
     * @var mixed
     */
    public $contentId;
}
