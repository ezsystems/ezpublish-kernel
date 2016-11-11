<?php

/**
 * UpdateContentTypeGroupSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * UpdateContentTypeGroupSignal class.
 */
class UpdateContentTypeGroupSignal extends Signal
{
    /**
     * ContentTypeGroupId.
     *
     * @var mixed
     */
    public $contentTypeGroupId;
}
