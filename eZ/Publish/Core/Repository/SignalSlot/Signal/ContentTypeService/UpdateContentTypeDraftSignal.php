<?php

/**
 * UpdateContentTypeDraftSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\Repository\SignalSlot\Signal;

/**
 * UpdateContentTypeDraftSignal class.
 */
class UpdateContentTypeDraftSignal extends Signal
{
    /**
     * ContentTypeDraftId.
     *
     * @var mixed
     */
    public $contentTypeDraftId;
}
