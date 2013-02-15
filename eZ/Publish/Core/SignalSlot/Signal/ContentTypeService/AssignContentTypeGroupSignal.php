<?php
/**
 * AssignContentTypeGroupSignal class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * AssignContentTypeGroupSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class AssignContentTypeGroupSignal extends Signal
{
    /**
     * ContentTypeId
     *
     * @var mixed
     */
    public $contentTypeId;

    /**
     * ContentTypeGroupId
     *
     * @var mixed
     */
    public $contentTypeGroupId;
}
