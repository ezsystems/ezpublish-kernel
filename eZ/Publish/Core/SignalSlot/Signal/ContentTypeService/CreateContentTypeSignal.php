<?php
/**
 * CreateContentTypeSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateContentTypeSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class CreateContentTypeSignal extends Signal
{
    /**
     * Content Type ID
     *
     * @var mixed
     */
    public $contentTypeId;
}
