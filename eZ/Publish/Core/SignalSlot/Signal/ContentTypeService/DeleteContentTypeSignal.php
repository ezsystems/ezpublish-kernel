<?php

/**
 * DeleteContentTypeSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteContentTypeSignal class.
 */
class DeleteContentTypeSignal extends Signal
{
    /**
     * ContentTypeId.
     *
     * @var mixed
     */
    public $contentTypeId;
}
