<?php
/**
 * LoadContentTypesSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * LoadContentTypesSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\ContentTypeService
 */
class LoadContentTypesSignal extends Signal
{
    /**
     * ContentTypeGroup
     *
     * @var eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public $contentTypeGroup;

}

