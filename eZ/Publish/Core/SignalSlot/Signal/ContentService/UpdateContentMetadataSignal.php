<?php
/**
 * UpdateContentMetadataSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateContentMetadataSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentService
 */
class UpdateContentMetadataSignal extends Signal
{
    /**
     * ContentId
     *
     * @var mixed
     */
    public $contentId;
}
