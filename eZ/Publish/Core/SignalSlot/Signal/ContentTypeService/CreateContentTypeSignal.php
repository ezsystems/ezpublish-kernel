<?php

/**
 * CreateContentTypeSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * CreateContentTypeSignal class.
 */
class CreateContentTypeSignal extends Signal
{
    /**
     * Content Type ID.
     *
     * @var mixed
     */
    public $contentTypeId;
}
