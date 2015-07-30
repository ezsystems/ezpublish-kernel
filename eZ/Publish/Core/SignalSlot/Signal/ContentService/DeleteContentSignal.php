<?php

/**
 * DeleteContentSignal class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Signal\ContentService;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * DeleteContentSignal class.
 */
class DeleteContentSignal extends Signal
{
    /**
     * ContentId.
     *
     * @var mixed
     */
    public $contentId;
}
