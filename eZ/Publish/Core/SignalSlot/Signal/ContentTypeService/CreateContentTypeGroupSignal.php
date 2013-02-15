<?php
/**
 * CreateContentTypeGroupSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateContentTypeGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class CreateContentTypeGroupSignal extends Signal
{
    /**
     * Group ID
     *
     * @var mixed
     */
    public $groupId;
}
